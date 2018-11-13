[The mysql Database Tables](<https://mariadb.com/kb/en/library/the-mysql-database-tables/>) 

[blog about mysql db](https://www.cnblogs.com/shengdimaya/p/6919055.html)

\#Start   

mysql databases 作用:   

[MyQL system-database](https://dev.mysql.com/doc/refman/5.6/en/system-database.html)

 主要是存储授权、特权和状态的一些信息   

\#Basic Knowledge   

​    `desc tables_priv`  展示表结构  

1.mysql.tables_priv   

​    可以对单个表进行特别的权限控制 比如设置一个表只能单独被某个用户操作。  

* Host：主机名； 

\* DB：数据库名； 

\* User：用户名； 

\* Table_name：表名 

\* Table_priv：对表进行操作的权限(Select,Insert,Update,Delete,Create,Drop,Grant,References,Index,Alter) 

\* Column_priv：对表中的数据列进行操作的权限(Select,Insert,Update,Rederences)； 

\* Timestamp：修改权限的时间 

\* Grantor：权限的设置者 

2.mysql.columns_priv  

\* Host：主机名；

\* DB：数据库名；

\* User：用户名；

\* Table_name：表名

\* Column_name:列名

\* Column_priv：对表中的数据列进行操作的权限(Select,Insert,Update,Rederences)；

\* Timestamp：修改权限的时间

\3. db   

 Host                  | char(60)      | NO   | PRI |         |       |

| Db                    | char(64)      | NO   | PRI |         |       |

| User                  | char(32)      | NO   | PRI |         |       |

| Select_priv           | enum('N','Y') | NO   |     | N       |       |

| Insert_priv           | enum('N','Y') | NO   |     | N       |       |

| Update_priv           | enum('N','Y') | NO   |     | N       |       |

| Delete_priv           | enum('N','Y') | NO   |     | N       |       |

| Create_priv           | enum('N','Y') | NO   |     | N       |       |

| Drop_priv             | enum('N','Y') | NO   |     | N       |       |

| Grant_priv            | enum('N','Y') | NO   |     | N       |       |

| References_priv       | enum('N','Y') | NO   |     | N       |       |

| Index_priv            | enum('N','Y') | NO   |     | N       |       |

| Alter_priv            | enum('N','Y') | NO   |     | N       |       |

| Create_tmp_table_priv | enum('N','Y') | NO   |     | N       |       |

| Lock_tables_priv      | enum('N','Y') | NO   |     | N       |       |

| Create_view_priv      | enum('N','Y') | NO   |     | N       |       |

| Show_view_priv        | enum('N','Y') | NO   |     | N       |       |

| Create_routine_priv   | enum('N','Y') | NO   |     | N       |       |

| Alter_routine_priv    | enum('N','Y') | NO   |     | N       |       |

| Execute_priv          | enum('N','Y') | NO   |     | N       |       |

| Event_priv            | enum('N','Y') | NO   |     | N       |       |

| Trigger_priv

 配置用户对数据库的权限和管理     

\4. engine_cost    

engine_name | varchar(64)   | NO   | PRI | NULL              |                             |

| device_type | int(11)       | NO   | PRI | NULL              |                             |

| cost_name   | varchar(64)   | NO   | PRI | NULL              |                             |

| cost_value  | float         | YES  |     | NULL              |                             |

| last_update | timestamp     | NO   |     | CURRENT_TIMESTAMP | on update CURRENT_TIMESTAMP |

| comment     | varchar(1024) | YES  |     | NULL              |                        

| engine_name | device_type | cost_name              | cost_value | last_update         | comment |

+-------------+-------------+------------------------+------------+---------------------+---------+

| default     |           0 | io_block_read_cost     |       NULL | 2018-04-16 22:23:53 | NULL    |

| default     |           0 | memory_block_read_cost |       NULL | 2018-04-16 22:23:53 | NULL   

io_block_read_cost:从磁盘度数据的代价.   

memory_block_read_cost:从内存读数据的代价.  

这个表功能是记录 engine的io代价.  

5.server_cost  

\* row_evaluate_cost (default 0.2) 计算符合条件的行的代价，行数越多，此项代价越大 

\* memory_temptable_create_cost (default 2.0) 内存临时表的创建代价 

\* memory_temptable_row_cost (default 0.2) 内存临时表的行代价 

\* key_compare_cost (default 0.1) 键比较的代价，例如排序 

\* disk_temptable_create_cost (default 40.0) 内部myisam或innodb临时表的创建代价 

\* disk_temptable_row_cost (default 1.0) 

​    内部myisam或innodb临时表的行代价 

`由上可以看出创建临时表的代价是很高的，尤其是内部的myisam或innodb临时表。 

这个表记录server的cpu代价 这些可以用来优化数据库  

6.mysql.event  

  这个表记录事件(定期执行一些mysql操作等等). 

7.func

\* name 

\* ret 

\* dl 

\* type 

这个表存放用户自定义创建函数的信息  语句:`create function` 

经典相关漏洞: [MySQL CREATE FUNCTION功能mysql.func表允许注入任意函数库漏洞](http://bbs.landingbj.com/t-0-247451-1.html) 



8.general_log   

*event_time 

*user_host  

*thread_id 

*server_id 

*command_type  

*argument 

*************************** 1. row ***************************

  event_time: 2014-11-11 08:40:04.117177

   user_host: root[root] @ localhost []

   thread_id: 74

   server_id: 1

command_type: Query

​    argument: SELECT * FROM test.s

当 log_output 启用的时候 记录数据库运行的具体情况  (默认关闭) 

set Global log_output = 'TABLE

set Global log_output = ‘FILE,TABLE’

FULUSH TABLES 清空该表数据

更多了解 

[writing-logs-into-tables](https://mariadb.com/kb/en/library/writing-logs-into-tables/)

9.slow_log 

start_time     | timestamp(6)        | NO   |     | CURRENT_TIMESTAMP(6) | on update CURRENT_TIMESTAMP(6) |

| user_host      | mediumtext          | NO   |     | NULL                 |                                

| query_time     | time(6)             | NO   |     | NULL                 |                                

| lock_time      | time(6)             | NO   |     | NULL                 |                                

| rows_sent      | int(11)             | NO   |     | NULL                 |                                

| rows_examined  | int(11)             | NO   |     | NULL                 |                                

| db             | varchar(512)        | NO   |     | NULL                 |                                

| last_insert_id | int(11)             | NO   |     | NULL                 |                                

| insert_id      | int(11)             | NO   |     | NULL                 |                                

| server_id      | int(10) unsigned    | NO   |     | NULL                 |                                

| sql_text       | mediumblob          | NO   |     | NULL                 |                                

| thread_id      | bigint(21) unsigned | NO   |     | NULL                 |                                

条件于general_log相同,但是记录的是慢查询操作(sql执行时间超过了一定限度) 

\10. gtid_executed 

| source_uuid    | char(36)   | NO   | PRI | NULL    |       |

| interval_start | bigint(20) | NO   | PRI | NULL    |       |

| interval_end   | bigint(20) | NO   |     | NULL    |       |

存储gtid事物信息 

\11. help_category help_keyword help_relation help_topic 



*help_category:关于帮助主题类别的信息 

*help_keyword:与帮助主题相关的关键字信息 

*help_relation:帮助关键字信息与主题信息之间的映射 

*help_topic:帮助主题的详细内容 

 存放的是myql的help命令的信息

help select  //使用方式

[具体了解](http://blog.itpub.net/28218939/viewspace-2158161)

12.innodb_index_stats innodb_table_stats



innodb_index_stats 存储的是索引的数据 `select * from innodb_index_stats`

| database_name    | varchar(64)         | NO   | PRI | NULL              |                             |

| table_name       | varchar(64)         | NO   | PRI | NULL              |                             |

| index_name       | varchar(64)         | NO   | PRI | NULL              |                             |

| last_update      | timestamp           | NO   |     | CURRENT_TIMESTAMP | on update CURRENT_TIMESTAMP |

| stat_name        | varchar(64)         | NO   | PRI | NULL              |                             |

| stat_value       | bigint(20) unsigned | NO   |     | NULL              |                             |

| sample_size      | bigint(20) unsigned | YES  |     | NULL              |                             |

| stat_description | varchar(1024)       | NO   |     | NULL              |                             |



database_name 数据库名

table_name 表名

index_name 索引名

last_update 最后一次更新时间

stat_name 统计名

stat_value 统计值

sample_size 样本大小

stat_description 统计说明-索引对应的字段名

这里会存储数据库所有表索引信息:

​    因为一般表都会有主键 就会有主键索引 也就是说这里可以获取到相应的表名  

innodb_table_stats

题外话:

 InnoDB引擎适用大量的insert或者update操作 

 MyISAM引擎适用大量的select 全文搜索能力的操作



Innodb_table_stats 

| database_name            | varchar(64)         | NO   | PRI | NULL              |                             |

| table_name               | varchar(64)         | NO   | PRI | NULL              |                             |

| last_update              | timestamp           | NO   |     | CURRENT_TIMESTAMP | on update CURRENT_TIMESTAMP |

| n_rows                   | bigint(20) unsigned | NO   |     | NULL              |                             |

| clustered_index_size     | bigint(20) unsigned | NO   |     | NULL              |                             |

| sum_of_other_index_sizes | bigint(20) unsigned | NO   |     | NULL              |                            

database_name 数据库名

table_name 表名

last_update 最后一次更新时间

n_rows 表中总有多少列数据

clustered_index_size 聚集索引大小(数据页)

sum_of_other_index_sizes 其他索引大小(数据页)



这里可以获取列数据 **n_rows** 

\13. ndb_binlog_index 

​    应用于MySQL Cluster(数据库集群的配置文件) 

​    [reference ndb_binlog_index](https://mariadb.com/kb/en/library/mysqlndb_binlog_index-table/)

14.slave_master_info, slave_relay_log_info, slave_worker_info 

​    这三个表结构是配置主/从分布式 mysql的时候启用

15.plugin

 name  | varchar(64)  | NO   | PRI |         |       |

| dl    | varchar(128) | NO   |     |         |       |

存放有关服务器插件的相关信息 

16.proc 

​    表名有点多,作用是存放存储过程和方法

\17. procs_priv

| Host         | char(60)                               | NO   | PRI |                   |                             |

| Db           | char(64)                               | NO   | PRI |                   |                             |

| User         | char(32)                               | NO   | PRI |                   |                             |

| Routine_name | char(64)                               | NO   | PRI |                   |                             |

| Routine_type | enum('FUNCTION','PROCEDURE')           | NO   | PRI | NULL              |                             |

| Grantor      | char(93)                               | NO   | MUL |                   |                             |

| Proc_priv    | set('Execute','Alter Routine','Grant') | NO   |     |                   |                             |

| Timestamp    | timestamp                              | NO   |     | CURRENT_TIMESTAMP | on update CURRENT_TIMESTAMP 

 存放存储过程和方法权限 具体信息如列所示 

\18. proxies_priv 

+-----------+------+--------------+--------------+------------+----------------------+---------------------+

| Host      | User | Proxied_host | Proxied_user | With_grant | Grantor              | Timestamp           |

+-----------+------+--------------+--------------+------------+----------------------+---------------------+

| localhost | root |              |              |          1 | boot@connecting host | 0000-00-00 00:00:00 |

+-----------+------+--------------+--------------+------------+----------------------+---------------------+



The mysql.proxies_priv table contains information about proxy privileges. The table can be queried and although it is possible to directly update it, it is best to use [GRANT](https://mariadb.com/kb/en/grant/) for setting privileges.

创建代理用户:

create User 'zhouProxy'@'localhost' identified with test_plugin_server as '12345';

grant proxy on zhou4@'localhost' to 'zhouProxy'@'localhost';

flush privileges;

4  测试，进入cmd下  mysql -uzhouProxy -p12345;

select @@proxy_user;

5 查看代理用户权限

show grants for zhouProxy@'localhost';

这个可以拷贝用户权限,我也没想到可以拿来干嘛用。 



19.servers (Miscellaneous System Tables) 

| Server_name | char(64) | NO   | PRI |         |       |

| Host        | char(64) | NO   |     |         |       |

| Db          | char(64) | NO   |     |         |       |

| Username    | char(64) | NO   |     |         |       |

| Password    | char(64) | NO   |     |         |       |

| Port        | int(4)   | NO   |     | 0       |       |

| Socket      | char(64) | NO   |     |         |       |

| Wrapper     | char(64) | NO   |     |         |       |

| Owner       | char(64) | NO   |     |         |       |

show engines 查看数据库引擎

Used by the FEDERATED storage engine

20.(Time Zone System Tables)
 

time_zone: Time zone IDs and whether they use leap seconds.

time_zone_leap_second: When leap seconds occur.

time_zone_name: Mappings between time zone IDs and names.

time_zone_transition, time_zone_transition_type: Time zone descriptions.



21.user 

| Host                   | char(60)                          | NO   | PRI |                       |       |

| User                   | char(32)                          | NO   | PRI |                       |       |

| Select_priv            | enum('N','Y')                     | NO   |     | N                     |       |

| Insert_priv            | enum('N','Y')                     | NO   |     | N                     |       |

| Update_priv            | enum('N','Y')                     | NO   |     | N                     |       |

| Delete_priv            | enum('N','Y')                     | NO   |     | N                     |       |

| Create_priv            | enum('N','Y')                     | NO   |     | N                     |       |

| Drop_priv              | enum('N','Y')                     | NO   |     | N                     |       |

| Reload_priv            | enum('N','Y')                     | NO   |     | N                     |       |

| Shutdown_priv          | enum('N','Y')                     | NO   |     | N                     |       |

| Process_priv           | enum('N','Y')                     | NO   |     | N                     |       |

| File_priv              | enum('N','Y')                     | NO   |     | N                     |       |

| Grant_priv             | enum('N','Y')                     | NO   |     | N                     |       |

| References_priv        | enum('N','Y')                     | NO   |     | N                     |       |

| Index_priv             | enum('N','Y')                     | NO   |     | N                     |       |

| Alter_priv             | enum('N','Y')                     | NO   |     | N                     |       |

| Show_db_priv           | enum('N','Y')                     | NO   |     | N                     |       |

| Super_priv             | enum('N','Y')                     | NO   |     | N                     |       |

| Create_tmp_table_priv  | enum('N','Y')                     | NO   |     | N                     |       |

| Lock_tables_priv       | enum('N','Y')                     | NO   |     | N                     |       |

| Execute_priv           | enum('N','Y')                     | NO   |     | N                     |       |

| Repl_slave_priv        | enum('N','Y')                     | NO   |     | N                     |       |

| Repl_client_priv       | enum('N','Y')                     | NO   |     | N                     |       |

| Create_view_priv       | enum('N','Y')                     | NO   |     | N                     |       |

| Show_view_priv         | enum('N','Y')                     | NO   |     | N                     |       |

| Create_routine_priv    | enum('N','Y')                     | NO   |     | N                     |       |

| Alter_routine_priv     | enum('N','Y')                     | NO   |     | N                     |       |

| Create_user_priv       | enum('N','Y')                     | NO   |     | N                     |       |

| Event_priv             | enum('N','Y')                     | NO   |     | N                     |       |

| Trigger_priv           | enum('N','Y')                     | NO   |     | N                     |       |

| Create_tablespace_priv | enum('N','Y')                     | NO   |     | N                     |       |

| ssl_type               | enum('','ANY','X509','SPECIFIED') | NO   |     |                       |       |

| ssl_cipher             | blob                              | NO   |     | NULL                  |       |

| x509_issuer            | blob                              | NO   |     | NULL                  |       |

| x509_subject           | blob                              | NO   |     | NULL                  |       |

| max_questions          | int(11) unsigned                  | NO   |     | 0                     |       |

| max_updates            | int(11) unsigned                  | NO   |     | 0                     |       |

| max_connections        | int(11) unsigned                  | NO   |     | 0                     |       |

| max_user_connections   | int(11) unsigned                  | NO   |     | 0                     |       |

| plugin                 | char(64)                          | NO   |     | mysql_native_password |       |

| authentication_string  | text                              | YES  |     | NULL                  |       |

| password_expired       | enum('N','Y')                     | NO   |     | N                     |       |

| password_last_changed  | timestamp                         | YES  |     | NULL                  |       |

| password_lifetime      | smallint(5) unsigned              | YES  |     | NULL                  |       |

| account_locked         | enum('N','Y')                     | NO   |     | N                     | 



这个表决定了用户权限和密码等等配置(类型大多是布尔) 