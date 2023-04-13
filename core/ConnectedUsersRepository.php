<?php

namespace Core;

use SplFixedArray;

class ConnectedUsersRepository
{
    /** @var SplFixedArray<array{fd: int, id: int}>  */
    private SplFixedArray $users;

    public function __construct()
    {
        $this->users = new SplFixedArray(0);
    }

    public function add(int $fd, int $id): void
    {
        $size = $this->users->count();
        $this->users->setSize($size + 1);
        $this->users[$size] = compact('fd', 'id');
    }

    public function remove(int $fd): void
    {
        foreach ($this->users as $key => $val) {
            if ($val['fd'] === $fd) {
                unset($this->users[$key]);
                return;
            }
        }
    }

    /**
     * @param int $fd
     * @return int id
     */
    public function get(int $fd): int
    {
        foreach ($this->users as $key => $val) {
            if ($val['fd'] === $fd) {
                return $this->users[$key]['id'];
            }
        }

        return -1;
    }
}