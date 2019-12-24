<?php

namespace Mitirrli\Hshmop;

class Hshmop
{
    protected $id;

    protected $shmId;

    protected $permission = 0755;

    protected $config = [];

    public function __construct(array $config = [])
    {
        if ($config) {
            $this->config = array_merge($this->config, $config);
        }

        $this->init();
    }

    public function init()
    {
        if (isset($this->config['id'])) {
            $this->id = $this->config['id'];
        } else {
            $this->generateId();
        }

        if (isset($this->config['permission'])) {
            $this->permission = $this->config['permission'];
        }

        $this->checkShmopArea();
    }

    public function checkShmopArea()
    {
        $this->shmId = @shmop_open($this->id, 'w', 0, 0);
    }

    public function put($data)
    {
        if (!$data) {
            throw new \Exception('No data');
        }

        if (is_array($data)) {
            $data = json_encode($data);
        }

        $dataSize = mb_strlen($data, 'utf-8');

        if ($this->shmId) {
            $this->clean();
            $this->close();
        }

        $this->shmId = shmop_open($this->id, 'c', $this->permission, $dataSize);

        shmop_write($this->shmId, $data, 0);

        return true;
    }

    public function get()
    {
        if(!$this->shmId){
            throw new \Exception('connot get data!');
        }

        $size = shmop_size($this->shmId);
        $data = shmop_read($this->shmId, 0, $size);

        return $data;
    }

    /**
     * clean data
     */
    public function clean()
    {
        shmop_delete($this->shmId);
    }

    /**
     * close shmop
     */
    public function close()
    {
        shmop_close($this->shmId);
    }

    public function getId()
    {
        return $this->id;
    }

    protected function generateId()
    {
        $id = ftok(__FILE__, 'b');

        $this->id = $id;
    }
}
