<?php

namespace Core\Swoole\Adapter;

use Core\Router\Request;
use JsonException;
use Swoole\WebSocket\Frame;

readonly class FrameToRequestAdapter extends Request
{
    /**
     * @throws InvalidRequest
     */
    public function __construct(Frame $frame)
    {
        try {
            $requestData = json_decode($frame->data, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new InvalidRequest('Invalid JSON');
        }

        if (!isset($requestData['action'])) {
            throw new InvalidRequest('Action field required');
        }
        parent::__construct($frame->fd, $requestData['data'] ?? [], $requestData['action']);
    }
}