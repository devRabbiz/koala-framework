<html>
    <head>
        <title>401 <?= $this->data->trlKwf('Unauthorized'); ?></title>
    </head>
    <body>
        <h1><?=$this->message;?></h1>
        <p><?= $this->data->trlKwf('You are not allowed to enter this page.'); ?></p>
    </body>
</html>
