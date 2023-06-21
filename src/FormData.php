<?php /** @noinspection PhpUnusedParameterInspection */

namespace CodeWorx\Http;

use ErrorException;
use Exception;
use LogicException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use UnexpectedValueException;
use function bin2hex;
use function random_bytes;

class FormData
{
    protected const EOL = "\r\n";

    protected const SEPARATOR = '--';

    /**
     * Holds the boundary
     *
     * @var string
     */
    protected $boundary;

    /**
     * Holds the content length
     *
     * @var int
     */
    protected $contentLength;

    /**
     * Whether the body is finished
     *
     * @var bool
     */
    protected $finished = false;

    /**
     * Holds the parts
     *
     * @var array
     */
    protected $parts = [];

    /**
     * Holds the part count
     *
     * @var int
     */
    protected $partCount = 0;

    /**
     * Holds the buffer read index
     *
     * @var int
     */
    protected $index = 0;

    /**
     * Holds the index of the part being read
     *
     * @var int
     */
    protected $partIndex;

    /**
     * MultipartBody constructor.
     *
     * @param array $fields
     *
     * @throws LogicException
     * @throws UnexpectedValueException
     * @throws Exception
     */
    public function __construct(array $fields = [])
    {
        $this->boundary = $this->generateBoundary();

        foreach ($fields as $name => $value) {
            $this->addField($name, $value);
        }
    }

    /**
     * Adds multiple string fields
     *
     * @param array $fields
     *
     * @return FormData
     * @throws LogicException
     * @throws UnexpectedValueException
     */
    public function addFields(array $fields): self
    {
        foreach ($fields as $name => $value) {
            $this->addField($name, $value);
        }

        return $this;
    }

    /**
     * Adds a string parameter.
     *
     * @param string $name  Field name
     * @param mixed  $value Field value
     *
     * @return FormData this object.
     * @throws LogicException
     * @throws UnexpectedValueException
     */
    public function addField(string $name, $value): self
    {
        $this->startPart();
        $this->addContentDisposition('form-data', $name);
        $this->endHeaders();
        $this->addContent($value);
        $this->endPart();

        return $this;
    }

    /**
     * Adds a file parameter
     *
     * @param string                   $name          Field name
     * @param string                   $filename      File name
     * @param string|resource|callable $content       File content. If it's a callable, it should take a length
     *                                                argument and return a string that is not larger than the input.
     * @param string                   $contentType   File content type.
     *
     * @return FormData this object.
     * @throws LogicException
     * @throws UnexpectedValueException
     */
    public function addFile(string $name, $content, string $contentType, ?string $filename = null): self
    {
        $this->startPart();
        $this->addContentDisposition('form-data', $name, $filename ?? $name);
        $this->addContentType($contentType);
        $this->endHeaders();
        $this->addContent($content);
        $this->endPart();

        return $this;
    }

    /**
     * Retrieves the content type of the body data
     *
     * @return string
     */
    public function getContentType(): string
    {
        return "multipart/form-data; boundary=$this->boundary";
    }

    /**
     * Finishes the multipart. Nothing can be added to it afterwards.
     *
     * @return self this object.
     * @throws LogicException
     * @throws UnexpectedValueException
     */
    final public function finish(): self
    {
        $this->add(static::SEPARATOR . $this->boundary . static::SEPARATOR . static::EOL);
        $this->finished = true;

        return $this;
    }

    /**
     * Whether the multipart is finished.
     *
     * @return boolean
     */
    final public function isFinished(): bool
    {
        return $this->finished;
    }

    /**
     * Retrieves the content length of the body data
     *
     * @return int
     */
    public function getContentLength(): int
    {
        return $this->contentLength;
    }

    /**
     * Reads a portion of this multipart object.
     *
     * @param int $length Maximum length of the portion to read.
     *
     * @return string A portion of this multipart object not larger than the given length, or an empty string if
     *                nothing remains to be read.
     * @throws LogicException
     * @throws UnexpectedValueException
     * @throws ErrorException
     * @throws RuntimeException
     */
    final public function read(int $length): string
    {
        if (! $this->finished) {
            $this->finish();
        }

        if ($length <= 0) {
            return '';
        }

        return $this->readChunk($length);
    }

    /**
     * cURL compatible version of the read method.
     *
     * @param resource $ch     cURL handle; ignored.
     * @param resource $fd     File descriptor passed to cURL by the CURLOPT_INFILE option; ignored.
     * @param int      $length Maximum length of the portion to read.
     *
     * @return string A portion of this multipart object not larger than the given length, or an empty string if
     *                nothing remains to be read.
     * @throws LogicException
     * @throws UnexpectedValueException
     * @throws ErrorException
     * @throws RuntimeException
     */
    final public function curl_read($ch, $fd, int $length): string
    {
        return $this->read($length);
    }

    /**
     * Buffers the content of this multipart object.
     * Note that this method should be called before calling read, otherwise the contents that have already read may
     * not be part of the buffered content. If the content is already buffered, this method will simply return the
     * buffered content.
     *
     * @param int $bufferSize
     *
     * @return string the content of this multipart object.
     * @throws ErrorException
     * @throws LogicException
     * @throws UnexpectedValueException
     * @throws RuntimeException
     */
    final public function buffer(int $bufferSize = 8192): string
    {
        if (! $this->finished) {
            throw new LogicException("can't buffer a non-finished multipart object");
        }

        return $this->bufferChunk($bufferSize);
    }

    /**
     * Whether or not the content is currently buffered
     *
     * @return boolean
     */
    final public function isBuffered(): bool
    {
        return (
            $this->partCount === 1 &&
            is_string($this->parts[0]) &&
            $this->contentLength === strlen($this->parts[0])
        );
    }

    /**
     * Returns this multipart object as a string. It will buffer the object to achieve this.
     * Note that this method should be called before calling read, otherwise the contents that have already read may
     * not be part of the result.
     *
     * @throws UnexpectedValueException
     * @throws ErrorException
     * @throws RuntimeException
     */
    final public function __toString(): string
    {
        return (string) $this->bufferChunk();
    }

    /**
     * Retrieves the boundary
     *
     * @return string
     */
    public function getBoundary(): string
    {
        return $this->boundary;
    }

    /**
     * Adds the content of a part.
     *
     * @param string|resource|callable $content Content. If it's a callable it should take a length argument and return
     *                                          a string that is not larger than the input.
     * @param int                      $length  Length of the part, or -1 if not known. Ignored if the part is a string.
     *
     * @throws LogicException
     * @throws UnexpectedValueException
     */
    final protected function addContent($content, int $length = -1): void
    {
        $this->add($content, $length);
    }

    /**
     * Adds a Content-ID header.
     *
     * @param string $contentID Content ID
     *
     * @throws LogicException
     * @throws UnexpectedValueException
     */
    final protected function addContentID(string $contentID): void
    {
        $this->add("Content-ID: $contentID" . static::EOL);
    }

    /**
     * Adds a Content-Type header.
     *
     * @param string $contentType Content type
     *
     * @throws LogicException
     * @throws UnexpectedValueException
     */
    final protected function addContentType(string $contentType): void
    {
        $this->add("Content-Type: $contentType" . static::EOL);
    }

    /**
     * @return string a newly generated random boundary.
     * @throws Exception
     */
    protected function generateBoundary(): string
    {
        return bin2hex(random_bytes(12));
    }

    /**
     * Starts a new part.
     *
     * @throws UnexpectedValueException
     * @throws LogicException
     */
    final protected function startPart(): void
    {
        $this->add(static::SEPARATOR . $this->boundary . static::EOL);
    }

    /**
     * Ends the last part.
     *
     * @throws UnexpectedValueException
     * @throws LogicException
     */
    final protected function endPart(): void
    {
        $this->add(static::EOL);
    }

    /**
     * Ends the headers.
     *
     * @throws UnexpectedValueException
     * @throws LogicException
     */
    final protected function endHeaders(): void
    {
        $this->add(static::EOL);
    }

    /**
     * Adds a piece of a part.
     *
     * @param StreamInterface|string|resource|callable $part   The part to add. If it's a callable it
     *                                                                           should take a length argument and
     *                                                                           return a string that is not larger
     *                                                                           than the input.
     * @param int                                                        $length The length of the part, or -1 if not
     *                                                                           known. Ignored if the part is a
     *                                                                           string.
     *
     * @throws LogicException
     * @throws UnexpectedValueException
     */
    protected function add($part, int $length = -1): void
    {
        if ($this->finished) {
            throw new LogicException('Multipart body is finished');
        }

        if (is_string($part)) {
            $length = strlen($part);
            $this->parts[] = $part;
            $this->partCount++;

            if ($this->contentLength !== -1) {
                $this->contentLength += $length;
            }

            return;
        }

        if ($part instanceof StreamInterface || is_resource($part) || is_callable($part)) {
            $this->parts[] = $part;
            $this->partCount++;

            if ($length === -1) {
                $this->contentLength = -1;
            } elseif ($this->contentLength !== -1) {
                $this->contentLength += $length;
            }

            return;
        }

        throw new UnexpectedValueException('non-supported part type: ' . gettype($part));
    }

    /**
     * Adds a Content-Disposition header.
     *
     * @param string $type     Content-Disposition type (e.g. form-data, attachment).
     * @param string $name     Value for any name parameter
     * @param string $filename Value for any filename parameter
     *
     * @throws LogicException
     * @throws UnexpectedValueException
     */
    final protected function addContentDisposition(string $type, string $name = '', string $filename = ''): void
    {
        $header = "Content-Disposition: $type";

        if ($name !== '') {
            $header .= '; name="' . $name . '"';
        }

        if ($filename !== '') {
            $header .= '; filename="' . $filename . '"';
        }

        $this->add($header . static::EOL);
    }

    /**
     * Performs a chunk read
     *
     * @param $length
     *
     * @return bool|mixed|string
     * @throws ErrorException
     * @throws UnexpectedValueException
     * @throws RuntimeException
     */
    protected function readChunk($length)
    {
        while ($this->index < $this->partCount) {
            $data = $this->readFromPart($length);

            if ($data !== '') {
                return $data;
            }

            $this->index++;
            $this->partIndex = 0;
        }

        return '';
    }

    /**
     * Reads from a body part
     *
     * @param int $length
     *
     * @return bool|mixed|string
     * @throws ErrorException
     * @throws UnexpectedValueException
     * @throws RuntimeException
     */
    protected function readFromPart(int $length)
    {
        $part = $this->parts[$this->index];

        if (is_string($part)) {
            $partLength = strlen($part);
            $length = min($length, $partLength - $this->partIndex);
            $result = $length === 0 ? '' : substr($part, $this->partIndex, $length);
            $this->partIndex += $length;

            return $result;
        }

        if ($this->parts[$this->index] instanceof StreamInterface) {
            /** @var StreamInterface $part */
            return $part->read($length);
        }

        if (is_resource($this->parts[$this->index])) {
            $result = fread($part, $length);

            if ($result === false) {
                throw new ErrorException(error_get_last()['message']);
            }

            return $result;
        }

        if (is_callable($this->parts[$this->index])) {
            return $part($length);
        }

        throw new UnexpectedValueException('non-supported part type: ' . gettype($this->parts[$this->index]));
    }

    /**
     * Buffers a chunk of the body
     *
     * @param int $bufferSize
     *
     * @return mixed
     * @throws ErrorException
     * @throws UnexpectedValueException
     * @throws RuntimeException
     */
    private function bufferChunk(int $bufferSize = 8192)
    {
        if (! $this->isBuffered()) {
            $this->index = 0;
            $this->partIndex = 0;
            $content = '';

            while (($data = $this->readChunk($bufferSize)) !== '') {
                $content .= $data;
            }

            $this->parts = [$content];
            $this->partCount = 1;
            $this->contentLength = strlen($content);
        }

        /** @noinspection SuspiciousAssignmentsInspection */
        $this->index = 0;
        $this->partIndex = 0;

        return $this->parts[0];
    }
}
