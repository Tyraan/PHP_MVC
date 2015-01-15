<?php
if(!isset($_GET['byOrder'])){
    $order='ASC';
}else{
    $order=explode(',', $_GET['byOrder'])[1];
}
if($order == "ASC" ){
    $order = "DESC";
}else{
    $order = "ASC";
}
?>
<table >
    <tr><th>名称</th>
    	<th>
        <a href="index.php?c=product&a=show&byOrder=<?php echo"price,$order"; ?>">商品价格</a>
        </th><th>商品说明</th>
        <th>
        <a href="index.php?c=product&a=show&byOrder=<?php echo"uptime,$order";?>">上架时间</a>
        </th>
        <th>商品分类</th><th>删除商品</th></tr>
        <?php
        if(!empty($_GET['products'])){
            $products = $_GET['products'];
            $url = toUrl('product', 'show');
            foreach ($products as $product) {
                echo"<tr>";
                echo "<form>";
                echo "</th><th>" . $product['name'] ;
                echo  " </th><th>" . $product['price'] ;
                echo  " </th><th>" . $product['description'] ;
                echo  "</th><th>" .date("Y-m-d H:i",$product['uptime']);
                if(isset($product['category'])){
                echo "</th><th>" .implode(',',$product['category']) . "</th><th>";
                }else{ echo "</th><th> 无</th><th>"; }
                $deleteUrl =  $url."id = ".$product['id'];
                echo "<button formaction='index.php?c=product&a=delete' type='submit' formmethod='POST' name = 'deleteProduct' value ={$product['id']}>删除</button>";
                echo "</form>";
                echo"</tr>";
            }
        }
        ?>

</table>
</h3>


