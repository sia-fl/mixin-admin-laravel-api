<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace App\Utils;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

class Res
{
    // status success 一般沒有返回值，直接使用
    public function ss($data = []): Response|Application|ResponseFactory
    {
        $data['code'] = 200;
        return response($data);
    }

    // status error
    public function se(array $data = []): JsonResponse
    {
        $data['code'] = $data['code'] ?? 500;
        $data['message'] = $data['message'] ?? '请求失败请重试';
        return response()->json($data, $data['code']);
    }

    // transaction
    public function tx($isSuccess, $onSuccess = null, $onFail = null): Application|ResponseFactory|Response|JsonResponse
    {
        if ($isSuccess) {
            if ($onSuccess) {
                $onSuccess();
            }
            return self::ss();
        }
        if ($onFail) {
            $onFail();
        }
        return self::se();
    }

    // 直接返回数据
    public function result($data): Response|Application|ResponseFactory
    {
        return self::ss(['result' => $data]);
    }

    // 上传后回调使用
    public function url($url): Response|Application|ResponseFactory
    {
        return self::result(['url' => $url]);
    }

    // 调整分页格式
    public function page(LengthAwarePaginator $paginator, $result = []): Response|Application|ResponseFactory
    {
        $result['items'] = $paginator->items();
        $result['total'] = $paginator->total();
        $result['perPage'] = $paginator->perPage();
        $result['lastPage'] = $paginator->lastPage();
        $result['currentPage'] = $paginator->currentPage();
        return self::result($result);
    }

    // 无限级
    public function treeOptions($item, $selectable = true): array
    {
        if ($item instanceof Collection) {
            $item = $item->toArray();
        }
        $result = [];
        for ($i = 0; $i < count($item); ++$i) {
            if (! $item[$i]['pid']) {
                $current = array_splice($item, $i--, 1)[0];
                $children = self::treeTn($item, $current['id']);
                $model = [
                    'title' => $current['name'],
                    'value' => $current['id'],
                    'key' => $current['id'],
                    'isLeaf' => false,
                    'selectable' => $selectable,
                ];
                if (count($children) || $i = 0) {
                    $model['children'] = $children;
                    $model['checkable'] = true;
                } else {
                    $model['checkable'] = false;
                }
                $result[] = array_merge($current, $model);
            }
        }
        return $result;
    }

    private function treeTn($item, $id = ''): array
    {
        $tn = [];
        for ($i = 0; $i < count($item); ++$i) {
            if ($item[$i]['pid'] == $id) {
                $current = array_splice($item, $i--, 1)[0];
                $children = self::treeTn($item, $current['id']);
                $model = [
                    'title' => $current['name'],
                    'value' => $current['id'],
                    'key' => $current['id'],
                    'isLeaf' => true,
                ];
                if (count($children)) {
                    $model['children'] = $children;
                    $model['isLeaf'] = false;
                    $model['selectable'] = true;
                } else {
                    $model['checkable'] = false;
                }
                $tn[] = array_merge($model, $current);
            }
        }
        return $tn;
    }
}
