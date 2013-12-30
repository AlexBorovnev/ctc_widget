<table style="border: solid #000000 1px;">
    <thead>
    <tr>
        <td>Shop ID</td>
        <td>Shop title</td>
        <td>Shop URL</td>
    </tr>
    </thead>
    <?php foreach ($shopsList as $shop): ?>
        <tr>
            <td><? if ($shopId != $shop['id']): ?><a
                    href="<?= HOST . 'admin/shop/' .$shop['id']; ?>"><?= $shop['id']; ?></a><? else: ?><?= $shop['id']; ?><? endif; ?></td>
            <td><?= $shop['title'] ?></td>
            <td><?= $shop['url'] ?></td>
        </tr>
    <? endforeach; ?>
</table>
<table style="border: solid #000000 1px;">
    <thead>
    <tr>
        <td>Widget ID</td>
        <td>Widget Type</td>
        <td>Widget Skin</td>
    </tr>
    </thead>
    <?php foreach ($widgetsList as $id => $widget): ?>
        <tr>
            <td><a href="#"><?= $id; ?></a></td>
            <td><?= $widget['typeId'] ?></td>
            <td><?= $widget['skinId'] ?></td>
        </tr>
    <? endforeach; ?>
</table>

<?php
/*
array(
    'id' => array(
        'positions' => array('id' => array('rule_type', 'source')),
        'skinId' => '',
        'typeId' => '',
        'commonRule' => array('categoryId', 'color')
    )
)
    */


