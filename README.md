# make-mscr
### 测试模块，请勿用于生产环境。
MSCR脚手架，一键生成 Model、Service、Controller、Request类文件。

``` bash
# php artisan make:mscr 数据库表名 文件名  数据库连接名（可选参数）
php artisan make:mscr user_info User/UserInfo database
```