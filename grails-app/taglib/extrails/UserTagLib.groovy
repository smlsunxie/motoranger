package extrails

class UserTagLib {
    def springSecurityService
    
    def emoticon = { attrs, body ->
       out << body() << (attrs.happy == 'true' ? " :-)" : " :-(")
    }
    
    def displayUserName = { attrs, body ->
    
        def output = ''
        
        try {
            def user = springSecurityService.currentUser
            
            def display =  user?.title
            
            output = display
            
        }
        catch (e) {
            log.error e.message
        }
            
        out << body() << output
    }

    def actionbar= {attrs, body ->

        def nextActionName

        if(attrs.actionName=="create" || attrs.actionName=="save")
            nextActionName="save";
        else if(attrs.actionName=="edit")
            nextActionName="update"
        else if(attrs.actionName=="show")
            nextActionName="edit"



        out << body() << render(template:'/component/actionbar'
            , model:[actionName:actionName, nextActionName:nextActionName ,domain:attrs?.domain])
    }
}
