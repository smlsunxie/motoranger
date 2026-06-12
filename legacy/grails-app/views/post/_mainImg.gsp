<g:if test="${post.mainImage}">
	<g:img uri="https://s3.amazonaws.com/upload.motoranger.net/attachment/${post.name}/${post.mainImage}" class="img-responsive" />  
</g:if>
<g:elseif test="${post?.product?.mainImage}">
  <g:img uri="https://s3.amazonaws.com/upload.motoranger.net/attachment/${post.product.name}/${post.product.mainImage}" class="img-responsive" />
</g:elseif>