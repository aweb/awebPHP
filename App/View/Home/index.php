<?php $this->layout('layout/head', ['title' => $title]) ?>
<style>
    body {
        font-size: 1.5em;
        color:#7d8492;
    }

    .box {
        width: 100%;
        height: 100%;
        position: relative;
    }

    .content {
        text-align: center;
        width: 80%;
        height: 50%;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
    }
</style>
<div class="box">
    <div class="content">
        <h1>Hello, <?= $this->e($name) ?></h1>
        <h2>Welcome Php Simple framework !</h2>
    </div
</div>