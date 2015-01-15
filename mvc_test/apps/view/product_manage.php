<?php echo loadView(array('title'=>'添加商品'), 'common_head')?>
<body>
<?php  require('product_show.php')?>
<h1>添加商品</h1>
	<hr>
	<form>
		<table>
			<tr>
				<td>商品名称:</td>
				<td><input type="text" value="" name="productName"></td>
			</tr>
			<tr>
				<td>商品价格:</td>
				<td><input tyep='number' value="" name="productPrice"></td>
			</tr>
			<tr>
				<td>商品描述:</td>
				<td><textarea name="productDescription" rows="5" cols="30"> </textarea></td>
			</tr>
			<tr>
				<td>商品类别：</td>
        <?php
								if (! empty ( $_GET ['catArray'] )) {
									$catArray = $_GET ['catArray'];
									echo "<td align='left'>";
									foreach ( $catArray as $cat ) {
										echo $cat ['name'];
										echo "<input type='checkbox' name ='category[]' value='{$cat['id']}' />";
									}
									echo "</td>";
								}
								?>

</td>
			</tr>
		</table>
		<input type="submit" value="添加" formmethod="POST"
			formaction="index.php?c=product&a=add" />
	</form>


<?php echo loadView(array(), 'common_body') ?>
</body>