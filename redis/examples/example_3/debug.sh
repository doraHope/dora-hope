#! /bin/bash
#文件名: debug.sh
count=0
while [ $count -lt 100 ]
do
	ret=`php /var/www/dora/redis/examples/example_3/demo.php`
	let count++
done
