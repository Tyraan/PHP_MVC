
<!---->
<!--/**-->
<!-- * Created by PhpStorm.-->
<!-- * User: Tyraan-->
<!-- * Date: 2015/1/4-->
<!-- * Time: 15:59-->
<!-- */-->
<ul>
    <li><a href="<?php echo toUrl('product', 'show'); ?>">管理商品</a></li>
    <li><a href="<?php echo toUrl('category', 'showCat'); ?>">管理商品分类</a></li>
    <li><a href="<?php echo toUrl('index','main'); ?>">主页</a></li>
    <li>时间 <?php print(date("Y-m-d H:i:s", time())) ;?> </li>
</ul>