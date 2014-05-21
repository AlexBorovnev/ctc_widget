#!/bin/sh
base_dir="/home/developer/dev/projects/sts.loc"
log_dir="${base_dir}/ozon/log"
list_dir="${base_dir}/ozon/list"
output_dir="${base_dir}/ozon/download"
# download_list - файл ссылок для скачивания
download_list="${list_dir}/download.lst"
# В active_list записываются активные закачки
active_list="${list_dir}/active.lst"
# В done_list записываются скачанные ссылки
done_list="${list_dir}/done.lst"
# В error_list записываются неудавшиеся закачки
error_list="${list_dir}/error.lst"
download_init_list="${list_dir}/download_init.lst"
shop_name="OZON.ru"
shop_url="http://www.ozon.ru/"
# $timeout - время перед повторной попыткой скачивания неудавшейся закачки
timeout=5

# Перемещает строку $1 из файла $2 в файл $3, нужна для манипуляций со списками
# move_line line source_file dest_file
move_line()
{
  if [ -f $2 ]; then
      tmp_file=`mktemp -t downloader.XXXXXX`
      echo $1 >> $3
      cat $2 | grep -v $1 > $tmp_file
      mv $tmp_file $2
  fi
}

# Функция скачивания, в $1 передается номер потока скачивания
download_thread()
{
  thread=$1
  # Цикл скачивания, пока файлы download.lst и error.lst не станут пустыми
  while [ -s $download_list ] || [ -s $error_list ]
  do  
    # Если download.lst пустой - переносим в него строку из error.lst
    if [ ! -s $download_list ]  
    then
      read url < $error_list
      move_line $url $error_list $download_list
      sleep $timeout
    fi
    read url < $download_list
    move_line $url $download_list $active_list
    # Старт закачки
    wget -c -o "${log_dir}/wget_thread${thread}.log" -O "${output_dir}/$(basename "$url")" $url
    # Проверка кода завершения wget (Если 0 - закачка успешная)
    if [ $? -eq 0 ]
    then
      # Закачка файла завершилась удачно
      move_line $url $active_list $done_list
      unzip -auq -d "${output_dir}" "${output_dir}/$(basename "$url")"
      rm "${output_dir}/$(basename "$url")"
      php "${base_dir}/ozon/cron.php" $shop_name $shop_url "ozon/download/$(basename "$url")"
      sleep $timeout
    else
      # Ошибка закачки - перемещаем в файл с ошибочными ссылками
      move_line $url $active_list $error_list
    fi
  done
  return 0
}

# Завершает ранее запущенные процессы скрипта и закачки из active.lst
stop_script()
{
  # Убиваем все процессы этого скрипта кроме текущего
  kill -9 `ps ax | grep $0 | grep -v "grep" | awk '{print $1}' | grep -v $$`
  # Убиваем все процессы закачек из active.lst
  while [ -s $active_list ]
  do
    read url < $active_list
    move_line $url $active_list $download_list
    kill -9 `ps ax | grep $url | grep -v "grep" | awk '{print $1}'`
  done
}

case "$1" in
"stop" ) 
  stop_script
  ;;
"start" )
   rm -f $download_list
   rm -f $error_list
   rm -f $active_list
   rm -f $done_list
   cp -f $download_init_list $download_list
  # Проверка наналичие файла со ссылками для скачивания
  if [ ! -e $download_list ];
  then
    exit
  fi
  if [ ! -e $error_list ]; then touch $error_list; fi
  if [ ! -e $active_list ]; then touch $active_list; fi
  if [ ! -e $done_list ]; then touch $done_list; fi
  if [ -e ${base_dir}/busy ];then
      exit;
  else
      touch ${base_dir}/busy
  fi
  # На случай вторичного запуска скрипта останавливаем ранее запущенные процессы
  #stop_script
  # Если не задано кол-во одновременных закачек в $2, устанавливаем 1 поток
    threads=1
  php "${base_dir}/ozon/cron.php" $shop_name $shop_url "before"
  sleep $timeout
  # Запускаем в фоне закачки
    download_thread &
    downloader_pid="${downloader_pid} $!"
    sleep 1

  # Ждем окончания всех закачек
  wait $downloader_pid
  sleep $timeout
  php "${base_dir}/ozon/cron.php" $shop_name $shop_url "after"
  # Все скачали...
  rm ${base_dir}/busy
  ;;
* )
  ;;
esac

return 0
