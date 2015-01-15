<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
<?php
/**
 * Created by PhpStorm.
 * User: Tyraan
 * Date: 2014/12/29
 * Time: 16:52
 */

?>
<h3 class  ='center'>
<table align="center" border="0" >
    <tr>
        <th>商品编号</th><th>名称</th></th><th>商品价格</th><th>上架时间</th><th>商品分类</th><th>删除商品</th>
        <?php
        if(!empty($_GET['productsArray'])) {
            $products = $_GET['productsArray'];
            $url = toUrl('product', 'show');
            foreach ($products as $product) {
                echo "<tr><th>" . $product['id'] ;
                echo "</th><th>" . $product['name'] ;
                echo  " </th><th>" . $product['price'] ;
                echo  "</th><th>" .$product['uploadtime'];
                echo "</th><th>" . $product['category'] . "</th><th>";
                $deleteUrl =  $url."id = ".$product['id'];
                echo "<a href = {$deleteUrl}? </a>删除该商品</th>";
            }
        }
?>
    </tr>
</table>
</h3>

</body>
</html>

