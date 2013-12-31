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
		        <tr>
		            <td><a href="#"><?= $id; ?></a></td>
		            <td><?= $this->typeList[$widget['typeId']] ?></td>
		            <td><?= $this->skinList[$widget['skinId']] ?></td>
		            <td>
		            	<a href="<?=makeLink('widget_id/' . $id)?>" target="_blank">Предпросмотр</a><br>
		            	<a href="#">Редактирование</a><br>
		            	<a href="#">Удалить</a>
		            </td>
		        </tr>
		    <? endforeach; ?>
		</table>	
		
		</div>
	</div>
	<div>
		<a href="<?=makeLink("/admin")?>">Назад</a>
	</div>
</div>