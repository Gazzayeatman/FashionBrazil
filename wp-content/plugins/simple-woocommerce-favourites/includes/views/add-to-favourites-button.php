<?php global $product; ?>
<div class='simple_container'>
	<span class='simple_message'></span>
	<button class="simple_add_to_favourites" data-productid='<?php echo $product->id; ?>'>Add to Favorites</button>
</div>

<style>
	.simple_container{
		text-align:right;
		margin-top:8px;
		margin-bottom:8px;
	}
	.simple_add_to_favourites{
		background-color:transparent;
		border:1px solid #262626;
		font-weight:bold;
		-webkit-transition: 200 ms all ease;
		transition: 200ms all ease;
		color:#262626;
		padding:8px;
	}
	.simple_add_to_favourites:hover{
		background-color:#262626;
		color:#fff;
	}
	.simple_message{
		background-color:#262626;
		font-weight:bold;
		color:#fff;
		padding:5px;
		border-radius:3px;
		display:none;
	}
</style>