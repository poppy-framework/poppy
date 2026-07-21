<?php

declare(strict_types = 1);

namespace Poppy\Area\Commands;

use Illuminate\Console\Command;
use Poppy\Area\Models\SysArea;
use Poppy\Core\Redis\RdsDb;

class InitCommand extends Command
{
    protected $name = 'py-area:init';

    private RdsDb $rds;

    public function handle()
    {
        $this->rds = RdsDb::instance();

        $this->initProvince();
        $this->initCity();
        $this->initCounty();

        $this->info('Clear Temp Cache Data ....');
        $this->rds->del([$this->ckProvince(), $this->ckCity()]);
        $this->info('Clear Temp Cache Data Success');
    }

    public function initProvince(): void
    {
        $this->info('Init Province Data ....');
        $path      = poppy_path('poppy.area', 'resources/def/province.json');
        $content   = app('files')->get($path);
        $provinces = json_decode($content, true);

        foreach ($provinces as $pro) {
            if (!SysArea::where('code', $pro['id'])->exists()) {
                SysArea::create([
                    'code'     => $pro['id'],
                    'title'    => $pro['name'],
                    'level'    => SysArea::LEVEL_PROVINCE,
                    'children' => '',
                ]);
            }
        }
        $kv = SysArea::whereRaw('right(code, 10) = "0000000000"')->pluck('id', 'code');
        $this->rds->hMSet($this->ckProvince(), $kv->toArray());
        $this->info('Init Province Data Success');
    }

    public function initCity()
    {
        $this->info('Init City Data ....');
        $path      = poppy_path('poppy.area', 'resources/def/city.json');
        $content   = app('files')->get($path);
        $provinces = json_decode($content, true);
        foreach ($provinces as $province_code => $city) {
            $provinceId = $this->rds->hGet($this->ckProvince(), $province_code);
            $insert     = [];
            foreach ($city as $ci) {
                $insert[] = [
                    'code'      => $ci['id'],
                    'parent_id' => $provinceId,
                    'title'     => $ci['name'],
                    'level'     => SysArea::LEVEL_CITY,
                    'children'  => '',
                ];
            }
            if (SysArea::where('parent_id', $provinceId)->exists()) {
                continue;
            }
            if (count($insert)) {
                SysArea::where('id', $provinceId)->update([
                    'has_child' => 1,
                ]);
                SysArea::insert($insert);
            }
        }
        $kv = SysArea::whereRaw('right(code, 8) = "00000000"')->where('parent_id', '!=', 0)->pluck('id', 'code');
        $this->rds->hMSet($this->ckCity(), $kv->toArray());
        $this->info('Init City Data Success');
    }

    public function initCounty()
    {
        $this->info('Init County Data ....');
        $path    = poppy_path('poppy.area', 'resources/def/county.json');
        $content = app('files')->get($path);
        $cities  = json_decode($content, true);
        foreach ($cities as $city_code => $counties) {
            $cityId = $this->rds->hGet($this->ckCity(), $city_code);
            $insert = [];
            foreach ($counties as $ci) {
                $insert[] = [
                    'code'      => $ci['id'],
                    'parent_id' => $cityId,
                    'title'     => $ci['name'],
                    'level'     => SysArea::LEVEL_COUNTY,
                    'children'  => '',
                ];
            }
            if (SysArea::where('parent_id', $cityId)->exists()) {
                continue;
            }
            if (count($insert)) {
                SysArea::where('id', $cityId)->update([
                    'has_child' => 1,
                ]);
                SysArea::insert($insert);
            }
        }
        $this->info('Init County Data Success');
    }

    private function ckProvince(): string
    {
        return 'py-area:import-province';
    }

    private function ckCity(): string
    {
        return 'py-area:import-city';
    }
}
