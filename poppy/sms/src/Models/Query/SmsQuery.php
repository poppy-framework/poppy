<?php

namespace Poppy\Sms\Models\Query;

use Illuminate\Support\Collection;
use Poppy\MgrApp\Classes\Grid\Query\QueryCustom;
use Poppy\Sms\Action\Sms;

class SmsQuery extends QueryCustom
{
    private Sms $sms;


    public function __construct()
    {
        $this->sms = new Sms();
    }

    public function get(): Collection
    {
        $templates = $this->sms->getTemplates();
        $scope     = $this->params['_scope'] ?? '';
        if ($scope) {
            return $templates->where('scope', $scope)->values();
        }
        return $templates->values();
    }
}