const __vite__mapDeps=(i,m=__vite__mapDeps,d=(m.f||(m.f=["./leaflet-src-DoZmeH3d.js","./rolldown-runtime-XKyaHtl9.js","./main-BwCE4sxw.js","./main-BW119va0.css"])))=>i.map(i=>d[i]);
import{r as e}from"./rolldown-runtime-XKyaHtl9.js";import{i as t,t as n}from"./main-BwCE4sxw.js";var r=class{constructor(e,t={}){this.containerId=e,this.container=document.getElementById(e),this.options={height:300,...t},this.map=null,this.markers=[],this.data=null,this.init()}async init(){if(this.container)try{await this.loadLeaflet(),this.generateSampleData(),this.createMap()}catch{this.showFallback()}}async loadLeaflet(){let t=document.createElement(`link`);t.rel=`stylesheet`,t.href=`https://unpkg.com/leaflet@1.9.4/dist/leaflet.css`,document.head.appendChild(t);let r=await n(()=>import(`./leaflet-src-DoZmeH3d.js`).then(t=>e(t.t(),1)),__vite__mapDeps([0,1]),import.meta.url);this.L=r.default||r}generateSampleData(){this.data=[{name:`United States`,code:`us`,visitors:4250,percentage:35.2,lat:39.8283,lng:-98.5795},{name:`United Kingdom`,code:`gb`,visitors:1890,percentage:15.7,lat:55.3781,lng:-3.436},{name:`Germany`,code:`de`,visitors:1420,percentage:11.8,lat:51.1657,lng:10.4515},{name:`France`,code:`fr`,visitors:980,percentage:8.1,lat:46.2276,lng:2.2137},{name:`Canada`,code:`ca`,visitors:760,percentage:6.3,lat:56.1304,lng:-106.3468},{name:`Australia`,code:`au`,visitors:650,percentage:5.4,lat:-25.2744,lng:133.7751},{name:`Japan`,code:`jp`,visitors:540,percentage:4.5,lat:36.2048,lng:138.2529},{name:`Netherlands`,code:`nl`,visitors:420,percentage:3.5,lat:52.1326,lng:5.2913},{name:`Italy`,code:`it`,visitors:380,percentage:3.2,lat:41.8719,lng:12.5674},{name:`Spain`,code:`es`,visitors:320,percentage:2.7,lat:40.4637,lng:-3.7492}]}createMap(){let e=this.container.closest(`.card`);if(e){let t=e.querySelector(`.card-footer`);this.container.innerHTML=``,this.container.style.height=`${this.options.height}px`,this.container.style.width=`100%`,this.container.style.position=`relative`,this.container.classList.remove(`d-flex`,`align-items-center`,`justify-content-center`,`bg-light`),this.map=this.L.map(this.container,{center:[20,0],zoom:2,scrollWheelZoom:!0,doubleClickZoom:!0,boxZoom:!0,keyboard:!0,zoomControl:!0}),this.L.tileLayer(`https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png`,{attribution:`© OpenStreetMap contributors`,maxZoom:18}).addTo(this.map),this.addDataPoints(),t&&(t.innerHTML=`
          <div class="row g-2 text-center">
            <div class="col-6 col-sm-3">
              <div class="text-primary fw-bold">${this.data.length}</div>
              <small class="text-muted d-block text-nowrap">Countries</small>
            </div>
            <div class="col-6 col-sm-3">
              <div class="text-success fw-bold">${this.data.reduce((e,t)=>e+t.visitors,0).toLocaleString()}</div>
              <small class="text-muted d-block text-nowrap">Visitors</small>
            </div>
            <div class="col-6 col-sm-3">
              <div class="text-warning fw-bold text-truncate">${this.data[0].name}</div>
              <small class="text-muted d-block text-nowrap">Top Country</small>
            </div>
            <div class="col-6 col-sm-3">
              <div class="text-info fw-bold">${this.data[0].percentage}%</div>
              <small class="text-muted d-block text-nowrap">Top Share</small>
            </div>
          </div>
        `)}else this.container.innerHTML=``,this.container.style.height=`${this.options.height}px`,this.container.style.width=`100%`,this.container.style.position=`relative`,this.container.classList.remove(`d-flex`,`align-items-center`,`justify-content-center`,`bg-light`),this.map=this.L.map(this.container,{center:[20,0],zoom:2,scrollWheelZoom:!0,doubleClickZoom:!0,boxZoom:!0,keyboard:!0,zoomControl:!0}),this.L.tileLayer(`https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png`,{attribution:`© OpenStreetMap contributors`,maxZoom:18}).addTo(this.map),this.addDataPoints();setTimeout(()=>{this.map&&this.map.invalidateSize()},100)}addDataPoints(){this.markers.forEach(e=>this.map.removeLayer(e)),this.markers=[],this.data.forEach(e=>{let t=Math.max(...this.data.map(e=>e.visitors)),n=8+e.visitors/t*17,r=this.L.divIcon({className:`custom-marker`,html:`
          <div style="
            width: ${n}px;
            height: ${n}px;
            background: #0d6efd;
            border: 2px solid white;
            border-radius: 50%;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: ${Math.max(8,n*.4)}px;
            font-weight: bold;
          ">
            <span class="fi fi-${e.code}" style="font-size: ${Math.max(6,n*.6)}px;"></span>
          </div>
        `,iconSize:[n,n],iconAnchor:[n/2,n/2]}),i=this.L.marker([e.lat,e.lng],{icon:r}).addTo(this.map);i.bindPopup(`
        <div class="p-2">
          <div class="d-flex align-items-center mb-2">
            <span class="fi fi-${e.code} me-2"></span>
            <strong>${e.name}</strong>
          </div>
          <div><strong>${e.visitors.toLocaleString()}</strong> visitors</div>
          <div class="text-muted">${e.percentage}% of total traffic</div>
        </div>
      `),this.markers.push(i)})}setupInteractions(){let e=this.container.closest(`.card`);e&&e.querySelectorAll(`.country-mini`).forEach(e=>{e.addEventListener(`click`,()=>{let t=e.dataset.country,n=this.data.find(e=>e.code===t);if(n){this.map.setView([n.lat,n.lng],5,{animate:!0,duration:1});let t=this.markers.find(e=>e.getLatLng().lat===n.lat&&e.getLatLng().lng===n.lng);t&&t.openPopup(),e.style.backgroundColor=`rgba(13, 110, 253, 0.1)`,e.style.borderRadius=`8px`,e.style.transition=`background-color 0.3s ease`,setTimeout(()=>{e.style.backgroundColor=``},300)}}),e.addEventListener(`mouseenter`,()=>{e.style.backgroundColor=`rgba(0, 0, 0, 0.05)`,e.style.borderRadius=`8px`,e.style.transition=`background-color 0.2s ease`}),e.addEventListener(`mouseleave`,()=>{e.style.backgroundColor=``})})}updateData(e){this.data=e,this.map&&this.createMap()}handleResize(){this.map&&this.map.invalidateSize()}showFallback(){this.container.innerHTML=`
      <div class="d-flex align-items-center justify-content-center h-100 bg-light rounded">
        <div class="text-center text-muted">
          <i class="fas fa-globe fa-3x mb-3 opacity-50"></i>
          <div class="h6">World Map Unavailable</div>
          <small>Unable to load map visualization</small>
        </div>
      </div>
    `}destroy(){this.map&&(this.map.remove(),this.map=null),this.markers=[],this.container&&(this.container.innerHTML=``)}getData(){return this.data}},i=class e{constructor(){this.widgets=new Map,this.charts=new Map,this.Chart=null,this.init()}async init(){await this.loadChartJS(),this.initializeCounters(),await this.initializeWidgetCharts(),await this.initializeTrafficChart(),await this.initializeWorldMap()}async loadChartJS(){let e=await n(()=>import(`./main-BwCE4sxw.js`).then(e=>e.r),__vite__mapDeps([2,1,3]),import.meta.url);this.Chart=e.Chart,this.Chart.register(...e.registerables)}initializeCounters(){let e=window.matchMedia(`(prefers-reduced-motion: reduce)`).matches,t=document.querySelectorAll(`.count, .card-title`),n=[];for(let e of t){let t=e.textContent.trim();if(!/^\d/.test(t))continue;let r=parseInt(t.replace(/,/g,``),10);Number.isFinite(r)&&(e.dataset.counterTarget=String(r),n.push(e))}if(e||n.length===0)return;let r=new IntersectionObserver((e,t)=>{for(let n of e)n.isIntersecting&&(this.runCounterAnimation(n.target),t.unobserve(n.target))},{threshold:.5});for(let e of n)r.observe(e)}runCounterAnimation(e){let t=Number(e.dataset.counterTarget);if(!Number.isFinite(t))return;let n=performance.now();e.textContent=`0`;let r=i=>{let a=Math.min(1,(i-n)/1500),o=1-(1-a)*(1-a);e.textContent=Math.floor(t*o).toLocaleString(),a<1?requestAnimationFrame(r):e.textContent=t.toLocaleString()};requestAnimationFrame(r)}initializeWidgetCharts(){this.Chart&&document.querySelectorAll(`[id^="widgetChart"]`).forEach((e,t)=>{try{this.createWidgetChart(e,t+1)}catch{}})}resolveWidgetChartValues(t,n){let r=t.dataset.values;if(r){let e=r.split(`,`).map(e=>Number(e.trim())).filter(e=>Number.isFinite(e));if(e.length>0)return e}return e.DEFAULT_CHART_DATA[(n-1)%e.DEFAULT_CHART_DATA.length]}createWidgetChart(e,t){if(!e)return;let n=e.getContext(`2d`);if(!n)return;this.charts.has(e.id)&&this.charts.get(e.id).destroy();let r=this.resolveWidgetChartValues(e,t),i=this.buildWidgetChartData(r),a=new this.Chart(n,{type:`line`,data:i,options:{responsive:!0,maintainAspectRatio:!1,interaction:{intersect:!1},plugins:{legend:{display:!1},tooltip:{enabled:!1}},scales:{x:{display:!1,grid:{display:!1}},y:{display:!1,grid:{display:!1}}},elements:{point:{radius:0,hoverRadius:0},line:{borderWidth:2,tension:.4}},animation:{duration:1e3}}});this.charts.set(e.id,a)}buildWidgetChartData(e){return{labels:Array(e.length).fill(``),datasets:[{data:e,borderColor:`rgba(255, 255, 255, 0.8)`,backgroundColor:`rgba(255, 255, 255, 0.1)`,fill:!1,pointRadius:0,pointHoverRadius:0}]}}initializeTrafficChart(){let e=document.getElementById(`trafficChart`);if(e&&this.Chart)try{let t=e.getContext(`2d`);this.charts.has(`trafficChart`)&&this.charts.get(`trafficChart`).destroy();let n=new this.Chart(t,{type:`line`,data:{labels:[`Jan`,`Feb`,`Mar`,`Apr`,`May`,`Jun`,`Jul`,`Aug`,`Sep`,`Oct`],datasets:[{label:`Visits`,data:[65,59,84,84,51,55,40,58,72,69],borderColor:`#007bff`,backgroundColor:`rgba(0, 123, 255, 0.1)`,fill:!0,tension:.4,pointRadius:4,pointHoverRadius:6},{label:`Unique Visits`,data:[28,48,40,59,86,27,90,45,62,58],borderColor:`#28a745`,backgroundColor:`rgba(40, 167, 69, 0.1)`,fill:!0,tension:.4,pointRadius:4,pointHoverRadius:6}]},options:{responsive:!0,maintainAspectRatio:!1,plugins:{legend:{display:!1},tooltip:{mode:`index`,intersect:!1}},scales:{x:{display:!0,grid:{display:!1},ticks:{color:`#6c757d`}},y:{display:!0,grid:{color:`rgba(0,0,0,0.1)`},ticks:{color:`#6c757d`}}},elements:{line:{borderWidth:2}},interaction:{mode:`nearest`,axis:`x`,intersect:!1}}});this.charts.set(`trafficChart`,n)}catch{}}handleResize(){this.charts.forEach(e=>{e&&typeof e.resize==`function`&&e.resize()})}initializeWorldMap(){let e=document.getElementById(`worldMap`);if(e)try{let e=new r(`worldMap`,{height:320,showCountryNames:!0,backgroundColor:`white`});this.widgets.set(`worldMap`,e)}catch{e.innerHTML=`
        <div class="d-flex align-items-center justify-content-center h-100 bg-light rounded">
          <div class="text-center text-muted">
            <i class="fas fa-globe fa-3x mb-3 opacity-50"></i>
            <div class="h6">Interactive World Map</div>
            <small>Global visitor statistics</small>
          </div>
        </div>
      `}}destroy(){this.charts.forEach(e=>{e&&typeof e.destroy==`function`&&e.destroy()}),this.widgets.forEach(e=>{e&&typeof e.destroy==`function`&&e.destroy()}),this.charts.clear(),this.widgets.clear()}};t(i,`DEFAULT_CHART_DATA`,[[65,59,84,84,51,55,40,65,59,84],[28,48,40,59,86,27,90,28,48,40],[45,25,16,36,67,18,76,45,25,16],[12,19,27,43,52,31,48,12,19,27]]);export{i as WidgetManager};
//# sourceMappingURL=widgets-BgFwgWrc.js.map