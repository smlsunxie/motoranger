



<sec:ifNotLoggedIn>
  <%--未登入--%>
  <li class="${controllerName=='user' && actionName=='create' ?'active':''} single">
    <g:link controller="user" action="create">
       <g:message code="default.register.text" />
      <i>register</i>
    </g:link>
  </li>
  <li class="${controllerName=='login' && actionName=='auth' ?'active':''} single">
    <g:link controller="login" action="auth">
       <g:message code="default.login.text" />
      <i>LOGIN</i>
    </g:link>
  </li>

</sec:ifNotLoggedIn>



<sec:ifLoggedIn>

  <g:if test="${actionName == 'index' && controllerName == 'store'}">
    <sec:ifAnyGranted roles="ROLE_MANERGER">
      <sec:ifNotSwitched>
        <li class="dropdown">
          <a class="dropdown-toggle" data-toggle="dropdown" href="#menu3">
            切換使用者
            <i>Switch</i>
          </a>

          <ul class="dropdown-menu">
            <g:each in="${operators}" var="operator">
              <li>
                <g:link url="${request.contextPath}/j_spring_security_switch_user?j_username=${operator.username}" >
                    ${operator?.title}
                </g:link>
              </li>

            </g:each>
          </ul>
        </li>
      </sec:ifNotSwitched>
    </sec:ifAnyGranted>
  </g:if>


  <li class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#menu3">
      <g:displayUserName />
      <i>ID</i>
    </a>

    <ul class="dropdown-menu">

      <sec:ifSwitched>
        <li>
          <g:link url='${request.contextPath}/j_spring_security_exit_user' >
              恢復原使用者
          </g:link>
        </li>
      </sec:ifSwitched> 



      <sec:ifNotSwitched>
        <li>
          <g:link uri="/j_spring_security_logout">
            <%--登出--%>
            <g:message code="default.logout.text" />
          </g:link>
        </li>
      </sec:ifNotSwitched>

    </ul>
  </li>
</sec:ifLoggedIn>
