package extrails

import grails.plugins.springsecurity.Secured
import org.grails.plugins.csv.CSVMapReader

class HomeController {
	static layout = 'bootstrap'
	def springSecurityService

    def index= {

        def unfinEvents
        def endEvents
        def store= springSecurityService.currentUser?.store

        def recentPosts = Post.findAll(max: 4, sort: 'dateCreated', order: 'desc')

        if(store){
            unfinEvents= Event.findAllByStatusAndStore(extrails.ProductStatus.UNFIN
                ,store,[order:"desc",sort:"lastUpdated"])
            endEvents= Event.findAllByStatusAndStore(extrails.ProductStatus.END
                ,store,[max:8,order:"desc",sort:"lastUpdated"])
        }else {
            unfinEvents= Event.findAllByStatus(extrails.ProductStatus.UNFIN
                ,[order:"desc",sort:"lastUpdated"])
            endEvents= Event.findAllByStatus(extrails.ProductStatus.END
                ,[max:8,order:"desc",sort:"lastUpdated"])
        }
        //add manager constrant
        // if(springSecurityService.currentUser){
        //     unfinEvents=unfinEvents.findAll{
        //         unfinEvent -> unfinEvent.store=springSecurityService.currentUser.store
        //     }
        //     endEvents=endEvents.findAll{
        //         endEvent -> endEvent.store=springSecurityService.currentUser.store
        //     }

        //     log.debug unfinEvents
        //     log.debug endEvents
        // }


        def operators=[]





        if(springSecurityService?.currentUser?.store 
            && springSecurityService?.currentUser.getAuthorities().contains(extrails.Role.findByAuthority('ROLE_MANERGER'))){

            def users=User.findAllByStore(springSecurityService?.currentUser?.store)

            users.each(){
                if(it.getAuthorities().contains(extrails.Role.findByAuthority('ROLE_OPERATOR'))){
                    operators << it
                }

            }

            // def query=UserRole.where{
            //     role==extrails.Role.findByAuthority('ROLE_OPERATOR')
            //     // user==springSecurityService.currentUser.store.id
            // }


            // operators= query.list().user

            // log.info operators.title
            // log.info operators.store
        }


        flash.message= "<a href=\"/post/show/7\">20120812:新增功能</a>"

        [
            recentPosts:recentPosts,
            unfinEvents:unfinEvents,
            endEvents:endEvents,
            // createProducts:createProducts,
            operators:operators
        ]


    }
    def question={
        
    }




    
}
