<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace App\Utils;

use Illuminate\Database\Eloquent\Collection;

class Format
{
    // 将数据转换成适用于前端的格式
    public static function options($item, $field = 'name'): array
    {
        if ($item instanceof Collection) {
            $item = $item->toArray();
        }
        $result = [];
        foreach ($item as $row) {
            $result[] = [
                'title' => $row[$field],
                'value' => $row['id'],
            ];
        }
        return $result;
    }

    /**
     * 将数据转换成，上下两级并存，他们的关系像是
     * 分类 - 商品
     * 部门 - 岗位
     * 而有时我们想要拿到 商品、岗位 这些 下一级选项
     * 我们需要 selectable 表示该选项不可选，且将 id 添加一个下划线
     * 便于一些场景分辨选项层级，例如我们允许选择上级，那么我们判断下划线开头则查询所有下级.
     */
    public static function optionNext($item, $foreignKey, $localField = 'name', $foreignField = 'name'): array
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
                    'children' => self::options($row[$foreignKey], $foreignField),
                ];
            }
        }
        return $result;
    }
}
