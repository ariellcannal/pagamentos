<?php
namespace PagamentosCannal\Entities;

class _EntityBase
{

    /**
     * Exporta as variáveis um array.
     *
     * @return array
     */
    public function importArray(array $array): self
    {
        foreach ($array as $key => $value) {
            $key = str_replace($this->prefix, '', $key);
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
        return $this;
    }

    /**
     * Exporta as variáveis um array.
     *
     * @return array
     */
    public function toArray($include_null = true): array
    {
        $return = [];
        $reflect = new \ReflectionClass($this);
        foreach ($reflect->getProperties() as $prop) {
            $key = $prop->getName();
            if (! in_array($key, [
                'prefix',
                'table'
            ])) {
                if (! $include_null && $this->$key === null) {
                    continue;
                } else {
                    $return[$this->prefix . $key] = $this->$key;
                }
            }
        }
        return $return;
    }

    public function import(self $obj, $include_null = false)
    {
        $this->importArray($obj->toArray($include_null));
        return $this;
    }
}

/* End of file _Entity.php */
/* Location: ./applicaion/core/_Entity.php */