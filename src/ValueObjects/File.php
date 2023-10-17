<?php

namespace Webeleven\EasyMutators\ValueObjects;

use Illuminate\Support\Arr;
use Webeleven\EasyMutators\MediaService;
use Webeleven\EloquentValueObject\ValueObject;

class File extends ValueObject
{

    protected $name;

    protected $filename;

    protected $extension;

    protected $size;

    protected $path;

    protected $basePath;

    protected $mimeType;

    public function __construct($value = null)
    {
        if (! is_array($value)) {
            $value = json_decode($value, true);
        }

        if ($value !== null) {
            $this->setData($value);
        }
    }

    protected function setData(array $data)
    {
        $this->name = Arr::get($data, 'name');
        $this->filename = Arr::get($data, 'filename');
        $this->size = Arr::get($data, 'size');
        $this->extension = Arr::get($data, 'extension');
        $this->path = Arr::get($data, 'path');
        $this->basePath = Arr::get($data, 'basePath');
        $this->mimeType = Arr::get($data, 'mimeType');
    }

    /**
     * @return mixed
     */
    public function toScalar()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'filename' => $this->filename,
            'path' => $this->path,
            'basePath' => $this->basePath,
            'size' => $this->size,
            'extension' => $this->extension,
            'mimeType' => $this->mimeType,
            'url' => $this->url()
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->toArray());
    }

    public function __set($key, $value)
    {
        $this->$key = $value;
    }

    public function __get($key)
    {
        if (method_exists($this, $key)) {
            return call_user_func([$this, $key]);
        }

        return $this->$key;
    }

    public function getMimeType()
    {
        return $this->mimeType;
    }

    public function path()
    {
        return $this->path;
    }

    public function basePath()
    {
        return $this->basePath;
    }

    public function url()
    {
        return $this->getStorageDisk()->url($this->path());
    }

    protected function getStorageDisk()
    {
        return app('filesystem')->disk(config('easymutators.storage_disk'));
    }

    public function download($name = null)
    {
        $file = $this->getStorageDisk()->get($this->path());

        $downloadName = ! empty($name) ? $name : $this->name;

        $downloadName .= sprintf('.%s', $this->extension);

        return response($file)
            ->header('Content-Type', $this->getMimeType())
            ->header('Content-Disposition', 'attachment; filename="' . $downloadName . '"');
    }

    public function downloadLink()
    {
        return $this->url();
    }

    public static function make($value = null, $mapping = null)
    {
        return app(MediaService::class)->makeMedia($value, $mapping);
    }
}