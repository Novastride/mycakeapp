<h2>「<?= $biditem->name ?>」の情報</h2>
<?= $this->Form->create($bidrequest) ?>
<fieldset>
    <legend><?= __('※入札を行う') ?></legend>
    <?php
        echo $this->Form->control('price');
    ?>
</fieldset>
<?= $this->Form->button(__('Submit')) ?>
<?= $this->Form->end() ?>
