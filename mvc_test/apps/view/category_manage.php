<?php echo loadView(array('title'=>'管理分类'), 'common_head') ?>
<body>
<h1> 管理分类 </h1>
<hr>
<table>
    <tr><th>分类名称</th><th>类别下商品</th><th>管理</th></tr>
    <?php
    if(!empty($_GET['catArray'])){
        $catArray = $_GET['catArray'];
        foreach( $catArray as $cat) {
            echo "<form >";
            echo "<tr><th>";
            echo $cat['name'];
            echo "</th><th>";
            if(!empty($cat['products'])){ echo implode(',',$cat['products']);}else{echo "无";}
            echo "</th><th>";
            echo "<button formaction='index.php?c=category&a=delete' type='submit' formmethod='POST' name = 'deleteCat' value ={$cat[id]}>删除</button>";
            echo "</th></tr>";
            echo "<form >";
        }
    }

    ?>
</table>
<h1> 添加分类 </h1>
<hr>
<form action='index.php?c=category&a=add'  method="post">
<table>
    <tr>
        <td>新增分类名</td>
        <td> <input type="text" name="catName" /></td>
    </tr>
    <tr><td><input type="submit" value="添加" formmethod="POST" formaction='index.php?c=category&a=add'/></td></tr>
    <!-- <Button type="submit" formaction="index.php?c=category&a=showCat" name="catName"   formmethod="POST">添加</Button>-->
</table>
</form>

<?php echo loadView(array(), 'common_body') ?>
</body>
