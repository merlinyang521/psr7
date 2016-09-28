<?php
namespace Kof\Psr7;

use OSS\OssClient;
use OSS\Core\OssException;
use InvalidArgumentException;
use RuntimeException;

class AliyunOssUploadedFile extends AbstractUploadedFile
{
    protected $ossClient = null;

    public function getOssClient()
    {
        if ($this->ossClient == null) {
            $this->ossClient = new OssClient(
                $this->getOption('accessKeyId'),
                $this->getOption('accessKeySecret'),
                $this->getOption('endpoint')
            );
        }

        return $this->ossClient;
    }

    /**
     * {@inheritdoc}
     *
     * @see http://php.net/is_uploaded_file
     * @see http://php.net/move_uploaded_file
     * @param string $targetPath Path to which to move the uploaded file.
     * @throws RuntimeException if the upload was not successful.
     * @throws InvalidArgumentException if the $path specified is invalid.
     * @throws RuntimeException on any error during the move operation, or on
     *     the second or subsequent call to the method.
     */
    public function moveTo($targetPath)
    {
        $this->validateActive();

        if (false === $this->isStringNotEmpty($targetPath)) {
            throw new InvalidArgumentException(
                'Invalid path provided for move operation; must be a non-empty string'
            );
        }

        $ossClient = $this->getOssClient();
        try {
            if ($this->file) {
                $ossClient->uploadFile($this->getOption('bucket'), $targetPath, $this->file);
            } else {
                $ossClient->putObject($this->getOption('bucket'), $targetPath, $this->getStream()->__toString());
            }
        } catch (OssException $e) {
            throw new RuntimeException(
                $e->getMessage(),
                $e->getCode()
            );
        }

        $this->moved = true;
    }
}
