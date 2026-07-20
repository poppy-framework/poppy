# Phpstan Extension Of Poppy Framework [READ ONLY]

> since 4.1 为了在项目中增加 phpstan 的静态检测
> 4.2.x 版本之后, Phpstan 移除扩展支持

## 安装

```
composer require poppy/ext-phpstan 4.2.*
```

`~/phpstan.neon` 中增加

```yaml
includes:
    - vendor/poppy/ext-phpstan/extension.neon
```

运行

```
$ phpstan analyse -c phpstan.neon
```
