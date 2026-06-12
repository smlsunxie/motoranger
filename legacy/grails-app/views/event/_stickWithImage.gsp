<div id="productStick" class="col-sm-6 col-md-3">

  <a class="block-stick-img">

    <g:img uri="https://s3.amazonaws.com/upload.motoranger.net/attachment/${eventInstance.product.name}/${eventInstance.product.mainImage}" onerror="this.src='/images/notFind.jpg'" class="img-thumbnail" />      

  </a>

  <g:render template="/event/stick" model="['stickName': stickName]" />
  

  <g:if test="${currentUserIsEventOwner[eventInstance.id]}">
    <sec:ifAnyGranted roles="ROLE_OPERATOR, ROLE_MANERGER">
      <div class="row stick_outside">

        <div class="col-sm-10 col-md-10 col-md-offset-1 col-sm-offset-1">
          <g:render template="/event/updateReceivedMoney" />
          <g:render template="/event/updateUnreceiveMoney" />
        </div>

      </div>
    </sec:ifAnyGranted>
  </g:if>

</div>
