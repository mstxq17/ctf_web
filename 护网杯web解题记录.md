<center>护网杯web解题记录</center>

## 一.easy_tornado

一开始看到题面torado

网址三个txt文件 第一个提示render()

第二个是sinature加密方式

第三个提示flag位置

 `md5(cookie_secret + md5(filename))` 

这个代码直接误导了我  选择了md5扩展攻击 选择循环爆破`secret`长度

```python
#! /usr/bin/python
# -*- coding:utf-8 -*-

import requests
import hashpumpy
import urllib

for i in range(1,30):
	m = hashpumpy.hashpump('7f2e4ea558df9459599c50f635b12068','327a6c4304ad5938eaf0efb6cc3e53dc','62b8e68cc6939db06689740d65fcc330',i)
	print i
	message = urllib.quote(urllib.unquote(m[1]))
	url = 'http://49.4.94.186:32009/file?filename=fllllllllllag&signature={}'.format(message)
	r1 = requests.get(url)
	print url
	print r1.text
	if 'wrong' not in r1.text:
		print 'ok'
```

但是很快就发现了%80直接提示400 如果传不入%80 那么md5扩展攻击是不行。 这个时候开始考虑render的模版注入攻击.



[tornado—web框架基础入门](http://blog.51cto.com/wengmengkai/1844886) 通过这个进行快速学习tornado的开发 

[tornado文档](http://www.tornadoweb.org/en/stable/)  通过阅读文档快速掌握对象

安装:

```
pip install tornado
```

入门肯定写个官方demo

```
import tornado.ioloop
import tornado.web
# import uimodules as md
# import uimethods as mt

class MainHandler(tornado.web.RequestHandler):
    def get(self):
        self.write("Hello, world")

settings = {
    'template_path': 'views',        # html文件
    'static_path': 'statics',        # 静态文件（css,js,img）
    'static_url_prefix': '/statics/',        # 静态文件前缀
    'cookie_secret': 'suoning',        # cookie自定义字符串加盐
    # 'xsrf_cookies': True,         # 防止跨站伪造
    # 'ui_methods': mt,           # 自定义UIMethod函数
    # 'ui_modules': md,           # 自定义UIModule类
}

application = tornado.web.Application([
    (r"/", MainHandler),
], **settings)

if __name__ == "__main__":
    application.listen(8888)
    tornado.ioloop.IOLoop.instance().start()
```

通过`application = tornado.web.Application([(r"/", MainHandler),], **settings)` 

很明显可以知道 cookie_secret 在RequestHandler这个对象里面

然后根据[Tornado小记 -- 模板中的Handler](https://www.cnblogs.com/bwangel23/p/4858870.html)

> handler 指向RequestHandler
>
> 而RequestHandler.settings又指向self.application.settings
>
> 所有handler.settings就指向RequestHandler.application.settings了！



就知道怎么通过模版注入 {{ handler.settings }}

模版注入绕过:[Flask/Jinja2模板注入中的一些绕过姿势](https://p0sec.net/index.php/archives/120/)

里面的payload 基本都包含 () 很明显题目过滤了 只保留了.  所以通过上面别名的方法来获得cookie_secret



# 阅读官方文档快速学习

**但我记录下如何进行查阅官方文档进行快速解题的方法(初试)**

第一次阅读文档建议先对照别人翻译的中文文档 大概了解内容然后再去读英文文档

[tornado中文文档](https://tornado-zh-cn.readthedocs.io/zh_CN/latest/guide.html)



文档结构:

* User's guide 用户手册 
* Web framework 网站框架
* HTTP servers and clients http服务之类的东西
* Asnchronous networking
* Coroutines and concurrency
* Integration with other services
* Utilities



###### 第一步了解程序结构:

​	选取User's guide目录下的 Structure of a Tornado web application 了解程序结构

* The **Application**  object  应用程序对象
* subclassging **RequestHandler**  子类
* Handing request input 处理输入请求
* Overiding Requests methods 覆盖Reques方法
* Error Handing 错误处理
* Redirection 重定向
* Asynchronous handlers 异步处理

```
A Tornado web application generally consists of one or more RequestHandler subclasses, an Application object which routes incoming requests to handlers, and a main() function to start the server.
```

可以看出网站通常有多个RequestHandler的子类 和 一个application对象构成

阅读可以知道Applicaiton对象 负责全局的设置

```
app = Application([url(r"/",MainHandler),]) application来定义路由调用RequestHandler子类MainHandler

```

> The [`Application`](http://www.tornadoweb.org/en/stable/web.html#tornado.web.Application) constructor takes many keyword arguments that can be used to customize the behavior of the application and enable optional features; see [`Application.settings`](http://www.tornadoweb.org/en/stable/web.html#tornado.web.Application.settings) for the complete list.

这句话说了Application可以定义程序行为设置 我们跟进去看看

[tornado.web.Application.settings](http://www.tornadoweb.org/en/stable/web.html#tornado.web.Application.settings)

读下定义可知:setting 主要是存放一些额外的定制参数

Authentication and security settings: 

```python
cookie_secret: Used by RequestHandler.get_secure_cookie and set_secure_cookie to sign cookies.
```

可以看到在认证和安全性定义了cookie_secret  RequestHandler.get_secure_cookie

通过定义的key生成安全cookie。

这里可以看出来 cookie_secret 存放了Application.settings里面

结构:`Application是全局对象 RequestHandler是个全局父类 Application 是全局父类的子类的实例化` 



模版注入的话  读下Templates and UI 

>* Configuring templates 模版配置
>* Template syntax 模版语法
>* Internationalization  国际化
>* UI modules UI模版(模块)



```
By default, Tornado looks for template files in the same directory as the .py files that refer to them. To put your template files in a different directory, use the template_path Application setting (or override RequestHandler.get_template_path if you have different template paths for different handlers).
```

可以看出模版的配置文件也是在Application对象里面

RequestHandler.get_template_path 读取其他路径模版文件

```
Control statements are surrounded by {% and %}, e.g., {% if len(items) > 2 %}. Expressions are surrounded by {{ and }}, e.g., {{ items[0] }}.
```



控制语法{% %} 表达式是{{ }} 这个点有些人不看文档会觉得这是两种兼容语法 不过他们是有特殊意义的

**重点来了**

```
Expressions can be any Python expression, including function calls. Template code is executed in a namespace that includes the following objects and functions (Note that this list applies to templates rendered using RequestHandler.render and render_string. If you’re using the tornado.template module directly outside of a RequestHandler many of these entries are not present).
```

`Expressions can be any Python expression, including function calls.` 

这句话表明了模版注入 可以直接操作任意python语句(命令执行 读取等等)

```
Template code is executed in a namespace that includes the following objects and functions (Note that this list applies to templates rendered using RequestHandler.render and render_string.
```

这句话表明了 如果是使用`RequestHandler.render` 进行模版渲染的话 模版的代码就可以执行Handler的成员对象 数据操作对象 （文档的Notes 一定要多看看）

```
handler: the current RequestHandler object
```

注意到别名 handler 代表了 当前的RequestHandler 对象

```

class EntryHandler(tornado.web.RequestHandler):
    def get(self, entry_id):
        entry = self.db.get("SELECT * FROM entries WHERE id = %s", entry_id)
        if not entry: raise tornado.web.HTTPError(404)
        self.render("entry.html", entry=entry)
```



 也就是`tornado.web.RequestHandler` 这个父类一个对象  然后可以就可以操作setting 这个成员属性

关于为啥可以调用application的setting 我们可以跟一下[tornado的源代码](http://www.tornadoweb.org/en/stable/_modules/tornado/web.html)





```python
[docs]class RequestHandler(object):
    """Base class for HTTP request handlers.

    Subclasses must define at least one of the methods defined in the
    "Entry points" section below.
    """
    SUPPORTED_METHODS = ("GET", "HEAD", "POST", "DELETE", "PATCH", "PUT",
                         "OPTIONS")

    _template_loaders = {}  # type: typing.Dict[str, template.BaseLoader]
    _template_loader_lock = threading.Lock()
    _remove_control_chars_regex = re.compile(r"[\x00-\x08\x0e-\x1f]")

    def __init__(self, application, request, **kwargs): //传入application对象
        super(RequestHandler, self).__init__()

        *self.application = application*
        self.request = request
        self._headers_written = False
        self._finished = False
        self._auto_finish = True
        self._transforms = None  # will be set in _execute
        self._prepared_future = None
        self._headers = None  # type: httputil.HTTPHeaders
        self.path_args = None
        self.path_kwargs = None
        self.ui = ObjectDict((n, self._ui_method(m)) for n, m in
                             application.ui_methods.items())
        # UIModules are available as both `modules` and `_tt_modules` in the
        # template namespace.  Historically only `modules` was available
        # but could be clobbered by user additions to the namespace.
        # The template {% module %} directive looks in `_tt_modules` to avoid
        # possible conflicts.
        self.ui["_tt_modules"] = _UIModuleNamespace(self,
                                                    application.ui_modules)
        self.ui["modules"] = self.ui["_tt_modules"]
        self.clear()
        self.request.connection.set_close_callback(self.on_connection_close)
        self.initialize(**kwargs)
```

Application 在RequestHandler的实例化中引入 (这个还得继续跟ReversibleRouter )

```python
class Application(ReversibleRouter):
   def __init__(self, handlers=None, default_host=None, transforms=None,
                 **settings):
        if transforms is None:
            self.transforms = []
            if settings.get("compress_response") or settings.get("gzip"):
                self.transforms.append(GZipContentEncoding)
        else:
            self.transforms = transforms
        self.default_host = default_host
        self.settings = settings
```

这里可以看到加载了传入的settings

```python
@property
    def settings(self):
        """An alias for `self.application.settings <Application.settings>`."""
        return self.application.settings //加载appliaction的setting字典

```



这里就可以看出来applicaiton 是requestHandler一个成员属性  所以说是可以调用的



## 二、LtShop

 这道题目常规的注册登陆账户

发现是个购买的问题

当时我看到的时候就想到了溢出(之前在一个金融赛买宠物的时候遇到过php的整形溢出)

所以我上去直接输入`number=1e100`  提示	`invalid` 然后就觉得不是这个点了跑去研究cookie了

然后折腾了好久 后来看到群里人都说溢出 (ps:还是自己太水了,对溢出没理解到位 *反省*)

这次自己决定深入分析下这个点:

**整形溢出原理**

有符号:  

​	int:  `2**31-1 =4294967295 `

​     long: `2**63-1=9223372036854775807`

​     lon long:`s**64-1=18446744073709551615`

**溢出原理是高位截断低位保留 ** （od debug 了解）

show an example: 

```c
unsigned short int  a=65535; ---1000 0000 0000 0000
int b;
b = a;
cin<<b<<endl;

output:0
强制转换int类型 就变成了 000 0000 0000 0000 1是高位所以被截断了

那么就可以知道了
65536 1000 0000 0000 0001 -> int : 1   ---000 0000 0000 0001
65537 1000 0000 0000 0010 -> int : 2   ---000 0000 0000 0010
```



**回到题目分析逻辑**

 1.余额20 可以买四包辣条 通过条件竞争可以最多买到15包左右 也就是2～3包辣条之王

​	  简单说下条件竞争原理:

​		 number变量 是全局的 允许多个线程同时访问的

​		所以到了判断逻辑那里正常的但是等进程同时结束就会变多了

2. 通过辣条数量兑换超过9999999包的辣条之王来兑换flag

模仿出题思路写个代码(c++)

```c++
# include <iostream>

int main()
{
    long long int number;
    long long int law;
    cin>>number;
    int lt = get_num_from_db(email);
    if(lt > (int) number*5):
    	cout<< "succeed message:number" <<endl;
    	law = number;
    else:
    	cout << "not enought" <<endl;
}
	
```

所以说下payload: 来源 

`s**64-1=18446744073709551615` 18446744073709551615/5 + 1 进入程序处理逻辑

`(18446744073709551615/5 + 1)* 5 =  18446744073709551615 + 5`

`1000 0000 0000 0000 0000 0000 0000 0000 0000 0000 0000 0101`

 强制int之后 

`0000 0000 0000 0000 0000 0000 0000 0101` 也就是5包辣条了

满足条件就ok 然后就flag了



## THINKING

​     自己已经好久没怎么刷ctf题了,自己的web水平也是越来越低

​     做起ctf感觉知识面窄 而且基础也不扎实,导致很吃力. 之前已经打算放弃web攻击这方面的

​     打算今年转下开发、机器学习 搞项目 学算法的 但是最近有很多质量很好的ctf比赛

​    虽然自己很菜 但是做起来还是能学到很多东西 所以还是抵不住诱惑跑来肝题了。

​    希望自己能走的更远把,不至于辜负三叶草流星师傅当初对我入门的帮助。



## THANKs

   感谢狗哥 Altman等师傅对我的指点,不至于全程打得很懵b,有点希望。





