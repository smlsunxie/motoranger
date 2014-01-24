package motoranger
import grails.plugin.springsecurity.annotation.Secured
class SearchController {
	static layout="bootstrap"

    @Secured(['ROLE_OPERATOR', 'ROLE_MANERGER'])
    def query(){

        def productSearchResult
        def userSearchResult

        params.max= 6

        if(params.q && params.q != ''){
            productSearchResult = Product.search(params.q+" OR *"+params.q+"*",params)
        }



        if(productSearchResult?.results.size() == 1){
        	def product = productSearchResult?.results.get(0)
        	redirect(controller:"product", action:'show', id: product.id)
        }
        else {
        	render (view:"result", model:[searchResult:productSearchResult])
        }


    }

    @Secured(['ROLE_CUSTOMER', 'ROLE_OPERATOR', 'ROLE_MANERGER'])
    def createOrLinkProductOwner(){
        println "params?.product?.id=" + params?.product?.id

        def product = Product.findById(params?.product?.id)
        def user = User.findByUsername(product?.name)

        if(!user){
            redirect controller: 'user', action: 'create', params: ['product.id': params?.product?.id]
        }else {
            redirect controller: 'user', action: 'edit', id: user.id, params: ['product.id': params?.product?.id]
        }
    }

}
