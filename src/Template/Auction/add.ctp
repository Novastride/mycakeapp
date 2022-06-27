<h2>商品を出品する</h2>
<?= $this->Form->create($biditem) ?>
<fieldset>
    <legend>※商品名と終了時刻を入力：</legend>
    <?php
        echo '<p><strong>USER: ' . $authuser['username'] . '</strong></p>';
        echo $this->Form->control('name');
        echo $this->Form->control('endtime');
    ?>
</fieldset>
<?= $this->Form->button(__('Submit')) ?>
<?= $this->Form->end() ?>
