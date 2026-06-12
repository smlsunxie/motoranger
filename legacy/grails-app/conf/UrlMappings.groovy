class UrlMappings {

	static mappings = {
        "/sitemap"{
            controller = 'sitemap'
            action = 'sitemap'
        }

		"/$controller/$action?/$id?/"{
			constraints {
				// apply constraints here
			}
		}

		"/" (controller: "home")

		
		"500"(view:'/error')

		// "500" (view: '/error500')
        // "500" (controller: 'error', action: 'serverError')
        
	    // "404" (controller: 'error', action: 'notFound')

	    "404"(view:'/notFound')



	}

}
