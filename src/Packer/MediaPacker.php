<?php
declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\Amiibo\Packer;

use Heptacom\HeptaConnect\Dataset\Ecommerce\Media\Media;
use Heptacom\HeptaConnect\Portal\Base\File\FileReferenceFactoryContract;
use Psr\Http\Message\UriInterface;

class MediaPacker
{
    private FileReferenceFactoryContract $file;

    public function __construct(FileReferenceFactoryContract $file)
    {
        $this->file = $file;
    }

    public function pack(UriInterface $imageUrl, string $imageMimetype): Media
    {
        $result = new Media();
        $result->setPrimaryKey((string) $imageUrl);
        $result->setFile($this->file->fromPublicUrl((string) $imageUrl));
        $result->setFilename(\basename($imageUrl->getPath()));
        $result->setMimeType($imageMimetype);

        return $result;
    }
}
