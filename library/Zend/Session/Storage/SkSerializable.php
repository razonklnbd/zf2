<?php
namespace Zend\Session\Storage;

abstract class SkSerializable implements StorageInterface {
    public function doSerialize(): array{
        // Get all accessible (public + protected + private) properties
        return get_object_vars($this);
    }
    public function doUnSerialize(array $data): void{
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }
    public function __serialize(): array
    {
        // Get all accessible (public + protected + private) properties
        return $this->doSerialize();
    }

    public function __unserialize(array $data): void
    {
        $this->doUnSerialize($data);
    }
}

