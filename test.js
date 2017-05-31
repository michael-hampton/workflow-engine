var y = [1, 2, 3];


$(".mike").on("click", function () {
	
	var removeItem = parseInt($(this).attr("id"));
  
  if($(this).hasClass("selected")) {
  		y.splice( $.inArray(removeItem,y) ,1 );
      $(this).removeClass("selected");
  } else {
  	y.push(removeItem);
     $(this).addClass("selected");
  }

	alert(y);
});

<div>
<a id="3" class="mike selected" href="#">Mike</a>
</div>
