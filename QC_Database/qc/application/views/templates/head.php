<?php
    $base_url = $this->config->item('base_url'); 
    $resources = $this->config->item('resources');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Dublin Core Metadata : http://dublincore.org/ -->
    <meta name="DC.title" content="QC Presentation">
    <meta name="DC.subject" content="Front end of the qc project">
    <meta name="DC.creator" content="Nima Esmaili Mokaram">

    <title><?php echo $title;?></title>

    <!-- Bootstrap -->
    <link href="<?php echo $resources;?>plugins/bootstrap/css/spacelab.css" rel="stylesheet">
    <link href="<?php echo $resources;?>plugins/overlay/style.css" rel="stylesheet">
    <link href="<?php echo $resources;?>plugins/fancybox/jquery.fancybox.css" rel="stylesheet">
    <link href="<?php echo $resources;?>css/style.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Raleway' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body id="body">