<g:set var="s3Service" bean="s3Service"/>
<div class="row">

  <div class="contact-info col-sm-4 col-md-4">
    <h2>外觀</h2>
    <g:img uri="/attachment/show?name=${product.name}&file=${product.mainImage}"  class="img-thumbnail" />
  </div>
  <div class="contact-info col-sm-4 col-md-4">
    <h2>產品資料</h2>
    <g:render template="/product/content" model="[product: product]" />
  </div>

  <g:if test="${params?.currentUserStoreId || params?.currentUserId == product?.user.id}">
    <sec:ifAnyGranted roles="ROLE_CUSTOMER">


      <div class="contact-info col-sm-4 col-md-4">
        <h2>車主資料</h2>
        
        <g:if test="${!product.user}">
          <div class="text-center">
            <g:link  class="btn btn-primary" controller="search" action="createOrLinkProductOwner" params="['product.id':product?.id]">
              <g:message code="search.createOrLinkProductOwner.label" />
            </g:link>
          </div>
        </g:if>
        <g:else>
          <g:render template="/user/content" model="[user: product.user, product: product]" />
        </g:else>
      </div>
    </sec:ifAnyGranted>
  </g:if>
</div>