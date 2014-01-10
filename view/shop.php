<script type="text/javascript" src="<?=HOST?>js/admin/system.js?<?=REV?>"></script>
<div class="wrapper">
	<div class="block">
		<div class="block-header">Магазин <?=$this->shopId?></div>
		<div class="block-content">
			<h4>Список виджетов</h4>
			<div ><a href="<?=makeLink('/admin/add/' . $this->shopId)?>" class="btn">Добавить новый</a></div>
			<table class="tbl t400 widgetTable">
		    <thead>
		    <tr>
		        <td style="width:50px">ID</td>
		        <td>Название</td>
		        <td style="width:100px">Тип</td>
		        <td style="width:100px">Скин</td>
		        <td style="width:200px">...</td>
		    </tr>
		    </thead>
		    <?php foreach ($this->widgetsList as $id => $widget): ?>
		        <tr data="<?=$id?>">
		            <td><a href="<?=makeLink('widget_id/' . $id)?>" target="_blank"><?= $id; ?></a></td>
		            <td><?= $widget['title'] ?></td>
		            <td><?= $this->typeList[$widget['typeId']] ?></td>
		            <td><?= $this->skinList[$widget['skinId']] ?></td>
		            <td>
		            	<a href="<?=makeLink('widget_id/' . $id)?>" target="_blank">Предпросмотр</a><br>
		            	<a class="dev_delete_widget" data="<?=$id?>" href="#">Удалить</a><br>
		            	<a href="<?=makeLink('admin/edit/' . $id)?>">Редактирование</a><br>
		            </td>
		        </tr>
		    <? endforeach; ?>
		</table>	
		
		</div>
	</div>
	<?=showPagination($this->currentPage, $this->pageCount, "admin/shop/" . $this->shopId . "/");?>
	   
    
    
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
