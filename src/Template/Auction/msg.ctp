<?php if (!empty($bidinfo)): ?>
<h2>商品「<?= h($bidinfo->biditem->name) ?>」</h2>
<h3>※メッセージ情報</h3>
<h6>※メッセージを送信する</h6>
<?= $this->Form->create($bidmsg) ?>
<?= $this->Form->textarea('message', ['row' => 2]) ?>
<?= $this->Form->button(__('Submit')) ?>
<?= $this->Form->end() ?>
<table cellpadding="0" cellspacing="0">
<thead>
    <tr>
        <th scope="row">送信者</th>
        <th scope="row" class="main">メッセージ</th>
        <th scope="row">送信時間</th>
    </tr>
</thead>
<tbody>
<?php if (!empty($bidmsgs)): ?>
    <?php foreach ($bidmsgs as $bidmsg): ?>
    <tr>
        <td><?= h($bidmsg->user->username) ?></td>
        <td><?= h($bidmsg->message) ?></td>
        <td><?= h($bidmsg->created) ?></td>
    </tr>
    <?php endforeach ?>
<?php else: ?>
    <tr><td colspan="3">※メッセージがありません。</td></tr>
<?php endif ?>
</tbody>
</table>
<?php else: ?>
<h2>※落札情報はありません。</h2>
<?php endif ?>
