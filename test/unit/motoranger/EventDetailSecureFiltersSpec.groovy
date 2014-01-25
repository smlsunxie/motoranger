package motoranger

import grails.test.mixin.TestMixin
import grails.test.mixin.support.GrailsUnitTestMixin
import spock.lang.Specification

/**
 * See the API for {@link grails.test.mixin.support.GrailsUnitTestMixin} for usage instructions
 */
@TestFor(EventDetailController)
@Mock([User, Store, Product, Part, Event, EventDetail, UserService, EventDetailSecureFilters])
class EventDetailSecureFiltersSpec extends Specification {

    def setup() {
		User.metaClass.encodePassword = {
			password = 'password'
		}

		EventDetail.metaClass.updateHeadTotalPrice = {
			
		}


		def storeA = new Store(name: "storeA", title: "storeA").save(failOnError: true)
		def storeB = new Store(name: "storeB", title: "storeB").save(failOnError: true)

		def userA = new User(username: 'userA', title: 'userA', password:'pass'
			, enabled: true, store: storeA).save(failOnError: true)
		def userB = new User(username: 'userB', title: 'userB', password:'pass'
			, enabled: true, store: storeB).save(failOnError: true)

		def productA = new Product(name: "productA", title: "productA", user: userA).save(failOnError: true)
		def productB = new Product(name: "productB", title: "productB", user: userB).save(failOnError: true)

		def eventA = new Event(name: 'eventA', date: new Date(), product: productA, user: userA, store: storeA).save(failOnError: true)
		def eventB = new Event(name: 'eventB', date: new Date(), product: productB, user: userB, store: storeB
			, status: motoranger.ProductStatus.END).save(failOnError: true)

		def partA = new Part(name: "partA", title: "partA", user: userA, store: storeA, price: 0, cost: 0).save(failOnError: true)
		def partB = new Part(name: "partB", title: "partB", user: userB, store: storeB, price: 0, cost: 0).save(failOnError: true)

		def eventDetailA = new EventDetail(name: "eventDetailA", title: "eventDetailA", head: eventA, part: partA).save(failOnError: true)
		def eventDetailB = new EventDetail(name: "eventDetailB", title: "eventDetailB", head: eventB, part: partB).save(failOnError: true)


	    UserService.metaClass.currentUser = {
        	userA
        }
    }

    def cleanup() {
    }


    void "使用者為 ROLE_OPERATOR 只可維護自己或所屬店家的事件細項"(){
    	setup: "取得相關 domain 實體，並且設定 params.id"
		    UserService.metaClass.isOperator = {
	        	true
	        }
		    UserService.metaClass.isCustomer = {
	        	false
	        }

    		def userA = User.findByUsername('userA')
			def eventA = Event.findByName('eventA')
			def eventB = Event.findByName('eventB')
			def partA = Part.findByName('partA')
			def partB = Part.findByName('partB')
			


		when: "userA 想要進行 create 屬於不同 store 的 eventDetailB 經過 filters"
			params["head.id"] = eventB.id
			params["part.id"] = partA.id
			response.reset()
	    	withFilters(controller: "eventDetail", action: "*") {
			    controller.create()
			}
		then: "將不允許進行維護，並且被 reditect 到所屬首頁，告知不可編輯"
			assert flash.message == "沒有權限建立不屬於自己店家的維修事件之維修項目"
			assert response.redirectedUrl == '/store/show/'+userA.store.id

		when: "userA 想要進行 create 屬於相同 store 的 eventDetailA 經過 filters"
			params["head.id"] = eventA.id
			params["part.id"] = partA.id
			response.reset()
	    	withFilters(controller:"eventDetail",action:"*") {
			    controller.create()
			}
		then: "將允許修改"
			assert model.eventDetail		
    }

    void "使用者為 ROLE_CUSTOMER 只可維護自己的維修項目"(){
    	setup: "取得相關 domain 實體，並且設定 params.id"
		    UserService.metaClass.isCustomer = {
	        	true
	        }

    		def userA = User.findByUsername('userA')
			def eventA = Event.findByName('eventA')
			def eventB = Event.findByName('eventB')
			def partA = Part.findByName('partA')
			def partB = Part.findByName('partB')
			
		when: "userA 想要進行 create 屬於不同 store 的 eventDetailB 經過 filters"
			params["head.id"] = eventB.id
			params["part.id"] = partA.id
			response.reset()
	    	withFilters(controller:"eventDetail",action:"*") {
			    controller.create()
			}
		then: "將不允許進行維護，並且被 reditect 到所屬首頁，告知不可編輯"
			assert flash.message == "沒有權限建立不屬於自己產品的維修事件之維修項目"
			assert response.redirectedUrl == '/user/show/'+userA.store.id

		when: "userA 想要進行 create 屬於相同 store 的 eventDetailA 經過 filters"
			params["head.id"] = eventA.id
			params["part.id"] = partA.id
			response.reset()
	    	withFilters(controller:"eventDetail",action:"*") {
			    controller.create()
			}
		then: "將允許修改"
			assert model.eventDetail	
			
    }    
}