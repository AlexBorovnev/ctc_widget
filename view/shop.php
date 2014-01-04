<script type="text/javascript" src="<?=HOST?>js/admin/system.js?<?=REV?>"></script>
<div class="wrapper">
	<div class="block">
		<div class="block-header">Магазин <?=$this->shopId?></div>
		<div class="block-content">
			<h4>Список виджетов</h4>
			<div ><a href="<?=makeLink('/admin/add')?>" class="btn">Добавить новый</a></div>
			<table class="tbl t400 widgetTable">
		    <thead>
		    <tr>
		        <td>ID</td>
		        <td>Тип</td>
		        <td>Скин</td>
		        <td>...</td>
		    </tr>
		    </thead>
		    <?php foreach ($this->widgetsList as $id => $widget): ?>
		        <tr data="<?=$id?>">
		            <td><a href="#"><?= $id; ?></a></td>
		            <td><?= $this->typeList[$widget['typeId']] ?></td>
		            <td><?= $this->skinList[$widget['skinId']] ?></td>
		            <td>
		            	<a href="<?=makeLink('widget_id/' . $id)?>" target="_blank">Предпросмотр</a><br>
		            	<a href="#">Редактирование</a><br>
		            	<a class="dev_delete_widget" data="<?=$id?>" href="#">Удалить</a>
		            </td>
		        </tr>
		    <? endforeach; ?>
		</table>	
		
		</div>
	</div>
    <div class="pagenation">
        <?php $cnt = $this->pageCount;
        for ($i = 1; $i <= $cnt; $i++) {
            if ($this->currentPage == $i){
                echo "<span>" . $i . "</span>";
                continue;
            }
            echo "<a href=" . makeLink("admin/shop/" . $this->shopId . "/" . $i) . ">" . $i . "</a>";
        }?>
    </div>
    <div>
		<a href="<?=makeLink("/admin")?>">Назад</a>
	</div>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        $('.dev_delete_widget').click(function(){
            var id = $(this).attr('data'),
                message = "Вы уверены, что хотите удалить виджет?";
            if (confirm(message)){
                api = new _api('<?=HOST?>' + '/handler');
                api.call('deleteWidget', {widgetId: id}, function (response){
                    $('tr[data='+id+']').remove();
                    window.location.reload();
                })
            }
        })
    })
</script>