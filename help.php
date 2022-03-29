<?php

declare(strict_types=1);

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

function rsaDecrypt($data)
{
    $prvKey = file_get_contents(storage_path('app/prv'));
    $data = base64_decode($data);
    openssl_private_decrypt($data, $text, $prvKey);
    return $text;
}

function rsaEncrypt($data)
{
    $pubKey = file_get_contents(storage_path('app/pub'));
    openssl_public_encrypt($data, $cipherText, $pubKey);
    return base64_encode($cipherText);
}

// status success
function ss($data = [])
{
    $data['code'] = 200;
    return response($data);
}

// status error
function se($data = [])
{
    $data['code'] = $data['code'] ?? 500;
    $data['message'] = $data['message'] ?? '请求失败请重试';
    return response()->json($data, $data['code']);
}

// transaction
function tx($isSuccess, $onSuccess = null, $onFail = null)
{
    if ($isSuccess) {
        if ($onSuccess) {
            $onSuccess();
        }
        return ss();
    }
    if ($onFail) {
        $onFail();
    }
    return se();
}

function result($data)
{
    return ss(['result' => $data]);
}

function upload($options, $file = null)
{
    if ($file === null) {
        $request = app('request');
        $file = $request->file('file');
    }
    $ext = $file->extension();
    $extType = $options['extType'];
    switch ($extType) {
        case 'image':
            if (! in_array($ext, ['jpg', 'jpeg', 'png'])) {
                throw new RuntimeException();
            }
            break;
        case 'video':
            if (! in_array($ext, ['mp4', 'avi'])) {
                throw new RuntimeException();
            }
            break;
        case 'excel':
            if (! in_array($ext, ['csv', 'xlsx'])) {
                throw new RuntimeException();
            }
            break;
        default:
            throw new RuntimeException();
    }
    $eid = Controller::companyId();
    $path = $options['path'];
    $fileId = uniqid();
    $filename = $file->getClientOriginalName();
    $filename = "{$eid}-{$fileId}-{$filename}";
    $filePath = "{$path}/{$filename}";
    $file->move(public_path($path), $filename);
    return $filePath;
}

function resultImg($imgUrl)
{
    return result(['img_url' => $imgUrl]);
}

function usePage()
{
    /** @var Request $request */
    $request = app('request');
    $page = $request->input('page', 0);
    $pageSize = $request->input('pageSize', 0);
    $columns = ['*'];
    return [$pageSize, $columns, 'page', $page];
}

function page(LengthAwarePaginator $paginator, $result = [])
{
    $result['pageCount'] = $paginator->lastPage();
    $result['page'] = $paginator->currentPage();
    $result['pageSize'] = $paginator->perPage();
    $result['list'] = $paginator->items();
    $result['total'] = $paginator->total();
    return result($result);
}

function treeN2Options($item, $foreignKey, $localField = 'name', $foreignField = 'name')
{
    if ($item instanceof Collection) {
        $item = $item->toArray();
    }
    $result = [];
    foreach ($item as $row) {
        if ($row[$foreignKey]) {
            $result[] = [
                'title' => $row[$localField],
                'value' => '_' . $row['id'],
                'selectable' => false,
                'children' => options($row[$foreignKey], $foreignField),
            ];
        }
    }
    return $result;
}

function options($item, $field = 'name')
{
    if ($item instanceof Collection) {
        $item = $item->toArray();
    }
    $result = [];
    foreach ($item as $row) {
        $result[] = [
            'title' => $row[$field],
            'label' => $row[$field],
            'value' => $row['id'],
        ];
    }
    return $result;
}

function treeOptions($item, $selectable = true)
{
    if ($item instanceof Collection) {
        $item = $item->toArray();
    }
    $result = [];
    for ($i = 0; $i < count($item); ++$i) {
        if (! $item[$i]['pid']) {
            $current = array_splice($item, $i--, 1)[0];
            $children = treeTn($item, $current['id']);
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

function treeTn($item, $id = '', $selectable = true)
{
    $tn = [];
    for ($i = 0; $i < count($item); ++$i) {
        if ($item[$i]['pid'] == $id) {
            $current = array_splice($item, $i--, 1)[0];
            $children = treeTn($item, $current['id']);
            $model = [
                'title' => $current['name'],
                'value' => $current['id'],
                'key' => $current['id'],
                'isLeaf' => true,
            ];
            if (count($children)) {
                $model['children'] = $children;
                $model['isLeaf'] = false;
                $model['selectable'] = $selectable;
            } else {
                $model['checkable'] = false;
            }
            $tn[] = array_merge($model, $current);
        }
    }
    return $tn;
}
