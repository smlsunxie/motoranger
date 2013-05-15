
<!DOCTYPE html>
<html>
<head>
<meta name="layout" content="bootstrap">

<g:set var="entityName" value="${message(code: 'post.label', default: '文章')}" />
<title><g:message code="default.list.label" args="[entityName]" /></title>


</head>
<body>

<div class="container">

                  <div class="row-fluid">
                    <table class="table">
                        <thead>
                          <tr>
                            <th>年度</th>
                            <th>總金額</th>


                          </tr>
                        </thead>
                        <tbody>
                            <g:each in="${resultList}" var="result" status="i">

                                <tr>

                                    <td><g:link controller="summary" action="turnoverMonthList" params="[year:result.year]">${result.year}</g:link></td>
                                    <td>${result.totalMoney}</td>  
                                  </tr>

                            </g:each>

                        </tbody>
                    </table>                    
                  </div>



</div>
</body>
</html>