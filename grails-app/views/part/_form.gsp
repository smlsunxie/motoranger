                <div class="control-group">
                    <label class="control-label required" for="name">

                        <g:message code="part.name.label" />
                        <%--必填--%>
                        <span class="required-mark">*</span>
                    </label>
                    <div class="controls">
                        <g:textField name="name" readonly value="${part?.name}" class="input input-xlarge" />
                    </div>
                </div>


                <div class="control-group">
                    <label class="control-label required" for="title">

                        <g:message code="part.title.label" />
                        <%--必填--%>
                        <span class="required-mark">*</span>
                    </label>
                    <div class="controls">
                        <g:textField name="title" value="${part?.title}" class="input input-xlarge" />
                    </div>
                </div>




               <div class="control-group advanced-region">
                  <label class="control-label" for="tags">
                      <%--tags--%>
                      <g:message code="default.tags.label" />
                  </label>


                  <div class="controls">
                    <ul name="tags" id='tag-field'>
                      <g:each in="${part.tags}">
                        <li>${it}</li>
                      </g:each>
                    </ul>
                  </div>

                </div>

                <div class="control-group">

                    <label class="control-label required" for="description">
                        <%--簡短敘述--%>
                        <g:message code="default.description.label" />

                    </label>
                    <div class="controls">
                        <g:textField name="description" value="${part?.description}" class="input input-xlarge" />
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="control-group">

                    <label class="control-label required" for="description">

                        <g:message code="part.cost.label" />

                    </label>
                    <div class="controls">
                        <g:field  type="number" name="cost" value="${part?.cost}" class="input input-xlarge" />
                        <span class="help-inline"></span>
                    </div>

                </div>

                <div class="control-group">

                    <label class="control-label required">

                        歷史成本

                    </label>

                    <div class="controls">
                        <h4>
                            <g:each var="it" in="${historyCost}">

                              <li class="btn btn-link" id='historyCost' data-historyCost='${it}' >${it}</li>

                            </g:each>
                        </h4>
                    </div>

                </div>

                <div class="control-group">

                    <label class="control-label required" for="description">

                        <g:message code="part.price.label" />

                    </label>
                    <div class="controls">
                        <g:field  type="number" name="price" value="${part?.price}" class="input input-xlarge" />
                        <span class="help-inline"></span>
                    </div>

                </div>
                <div class="control-group">

                    <label class="control-label required">

                        歷史售價

                    </label>
                    <div class="controls">
                        <h4>
                            <g:each var="it" status="i" in="${historyPrice}">

                              <li class="btn btn-link" id='historyPrice' data-historyPrice='${it}' >${it}</li>

                            </g:each>
                        </h4>
                    </div>

                </div>
                <div class="control-group">

                    <label class="control-label required" for="description">

                        <g:message code="part.stockCount.label" />

                    </label>
                    <div class="controls">
                        <g:field  type="number" name="stockCount" value="${part?.stockCount}" class="input input-xlarge" />
                        <span class="help-inline"></span>
                    </div>

                </div>
                
            <div class="control-group">
                <label class="control-label required" for="description">
                    <g:message code="user.store.label" default="Store" />

                </label>
                <div class="controls">
                   <g:select id="store" name="store.id" from="${motoranger.Store.list()}" optionKey="id" value="${part?.store?.id}" class="many-to-one" noSelection="['null': '']"/>
                </div>
            </div>

                <div class="control-group">

                    <label class="control-label required" for="fileupload">

                        <g:message code="default.imageUpload.label" />

                    </label>

                    <g:render template="/attachment/uploadBtn" model="[name:part.name ,mainImage: part?.mainImage]" />                   
                </div>

    <r:script>


      $(function() {

        $("ul[name='tags']").tagit({select:true, tagSource: "${g.createLink(controller:'tag',action: 'listAsJson')}"});

        $("li[id ='historyCost']").on('click',function(eventObject){
            var historyCost = $(this).attr("data-historyCost");
            $("#cost").val(historyCost)
        });

        $("li[id ='historyPrice']").on('click',function(eventObject){
            var historyCost = $(this).attr("data-historyPrice");
            $("#price").val(historyCost)
        });

      });


    </r:script>