package motoranger

class SitemapController {

    def sitemap = {
        render(contentType: 'text/xml', encoding: 'UTF-8') {
            mkp.yieldUnescaped '<?xml version="1.0" encoding="UTF-8"?>'
            urlset(xmlns: "http://www.sitemaps.org/schemas/sitemap/0.9",
                    'xmlns:xsi': "http://www.w3.org/2001/XMLSchema-instance",
                    'xsi:schemaLocation': "http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd") {
                url {
                    loc(g.createLink(absolute: true, controller: 'home', action: 'index'))
                    changefreq('hourly')
                    priority(1.0)
                }
                url {
                    loc(g.createLink(absolute: true, controller: 'store', action: 'index'))
                    changefreq('hourly')
                    priority(1.0)
                }
                url {
                    loc(g.createLink(absolute: true, controller: 'post', action: 'show', id:4))
                    changefreq('hourly')
                    priority(1.0)
                }                                
                url {
                    loc(g.createLink(absolute: true, controller: 'login', action: 'auth'))
                    changefreq('hourly')
                    priority(1.0)
                }

                url {
                    loc(g.createLink(absolute: true, controller: 'user', action: 'create'))
                    changefreq('hourly')
                    priority(1.0)
                }
                url {
                    loc(g.createLink(absolute: true, controller: 'home', action: 'question'))
                    changefreq('hourly')
                    priority(1.0)
                }

                           
                // SomeDomain.list().each {domain->
                // url {
                //     loc(g.createLink(absolute: true, controller: 'some', action: 'view', id: domain.id))
                //     changefreq('hourly')
                //     priority(0.8)
                // }
            }
        }
    }
}
