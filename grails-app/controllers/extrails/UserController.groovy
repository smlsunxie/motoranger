package extrails

import org.springframework.dao.DataIntegrityViolationException
import grails.plugins.springsecurity.Secured
import org.codehaus.groovy.grails.plugins.springsecurity.SpringSecurityUtils
class UserController {


    static allowedMethods = [save: "POST", update: "POST", delete: "POST"]

    def springSecurityService

    def index() {
        redirect(action: "list", params: params)
    }

    def list(Integer max) {
        params.max = Math.min(max ?: 10, 100)
        [userInstanceList: User.list(params), userInstanceTotal: User.count()]
    }

    def create() {

        [userInstance: new User(params),roles: Role.list(),storeList:storeList()]
    }

    def save() {
        def userInstance = new User(params)
        if (!userInstance.save(flush: true)) {
            render(view: "create", model: [userInstance: userInstance,roles: Role.list()])
            return
        }

        // 登入使用者若屬於 ROLE_MANERGER 則進行  userRoles 的儲存
        if(SpringSecurityUtils.ifAnyGranted("ROLE_MANERGER")){    
            params.list('userRoles').each(){
                if(!UserRole.create(userInstance,Role.findByAuthority(it),true)){
                    render(view: "create", model: [userInstance: userInstance,roles: Role.list()])
                    return                    
                }
            }
        }

        flash.message = message(code: 'default.created.message', args: [message(code: 'user.label', default: 'User'), userInstance.id])
        redirect(action: "show", id: userInstance.id)
    }

    def show(Long id) {
        def userInstance = User.get(id)
        if (!userInstance) {
            flash.message = message(code: 'default.not.found.message', args: [message(code: 'user.label', default: 'User'), id])
            redirect(action: "list")
            return
        }

        [userInstance: userInstance]
    }

    def edit(Long id) {
        def userInstance = User.get(id)


        if (!userInstance) {
            flash.message = message(code: 'default.not.found.message', args: [message(code: 'user.label', default: 'User'), id])
            redirect(action: "list")
            return
        }
        
        [userInstance: userInstance,roles: Role.list()
        ,userRoles:UserRole.findAllByUser(userInstance)
        ,storeList:storeList()]
    }

    @Secured(['ROLE_OPERATOR','ROLE_MANERGER'])
    def update(Long id, Long version) {
        def userInstance = User.get(id)
        if (!userInstance) {
            flash.message = message(code: 'default.not.found.message', args: [message(code: 'user.label', default: 'User'), id])
            redirect(action: "list")
            return
        }

        if (version != null) {
            if (userInstance.version > version) {
                userInstance.errors.rejectValue("version", "default.optimistic.locking.failure",
                          [message(code: 'user.label', default: 'User')] as Object[],
                          "Another user has updated this User while you were editing")
                render(view: "edit", model: [userInstance: userInstance])
                return
            }
        }


        userInstance.properties = params

        if (!userInstance.save(flush: true)) {
            render(view: "edit", model: [userInstance: userInstance])
            return
        }

        // 登入使用者若屬於 ROLE_MANERGER 則進行  userRoles UserRole Update
        if(SpringSecurityUtils.ifAnyGranted("ROLE_MANERGER")){
            UserRole.removeAll(userInstance)    
            params.list('userRoles').each(){
                if(!UserRole.create(userInstance,Role.findByAuthority(it),true)){
                    render(view: "edit", model: [userInstance: userInstance])
                    return                    
                }
            }
        }



        flash.message = message(code: 'default.updated.message', args: [message(code: 'user.label', default: 'User'), userInstance.id])
        redirect(action: "show", id: userInstance.id)
    }

    def delete(Long id) {
        def userInstance = User.get(id)
        if (!userInstance) {
            flash.message = message(code: 'default.not.found.message', args: [message(code: 'user.label', default: 'User'), id])
            redirect(action: "list")
            return
        }

        try {
            def userRoleInstances=UserRole.findAllByUser(userInstance)

            userRoleInstances?.each(){
                it.delete()
            }
            userInstance.delete(flush: true,failOnError:true)
            flash.message = message(code: 'default.deleted.message', args: [message(code: 'user.label', default: 'User'), id])
            redirect(action: "list")
        }
        catch (DataIntegrityViolationException e) {
            flash.message = message(code: 'default.not.deleted.message', args: [message(code: 'user.label', default: 'User'), id])
            redirect(action: "show", id: id)
        }
    }

    def storeList(){
        def storeList=[]
        if(SpringSecurityUtils.ifAnyGranted("ROLE_MANERGER") 
            && springSecurityService?.currentUser?.store){  
            
            storeList << springSecurityService.currentUser.store
        
        }else if(SpringSecurityUtils.ifAnyGranted("ROLE_ADMIN"))
            storeList=Store.list()

        log.debug storeList
        return storeList
    }
}
