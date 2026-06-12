<!DOCTYPE html>
<html>
  
  <head>
    <meta name="layout" content="bootstrap">
    <g:set var="entityName" value="${message(code: 'user.label', default: 'user')}" />
    <title>註冊</title>
  </head>

  <body>
    <g:render template="/component/formCreate" model="[domainName: 'user', , domain: userInstance]"/>
  </body>

</html>
