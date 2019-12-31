const fs = require('fs')

const template = fs.readFileSync('./build/index.html', 'UTF-8')
  .replace(/href="([^"]+)"/g, (match, url) => `href="/taoSystemStatus/views/js/build${url}"`)
  .replace(/src="([^"]+)"/g, (match, url) => `src="/taoSystemStatus/views/js/build${url}"`)

fs.writeFileSync('../templates/Status/index.tpl', template, 'UTF-8')
