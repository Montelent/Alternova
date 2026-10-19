{{-- Auto-save drafts (DOM+Livewire, offline-first) --}}
<script>
(function(){
if(window.__alternovaAutosaveV2)return;window.__alternovaAutosaveV2=true;
var S={cfg:null,lt:null,st:null,lj:'',sj:'',sa:0,el:null,on:navigator.onLine!==false,rest:{},bound:false};
function isPage(){return!!document.querySelector('.fi-resource-create-record-page,.fi-resource-edit-record-page')}
function findWire(){
  if(!window.Livewire)return null;
  try{
    var nodes=document.querySelectorAll('[wire\\:id]');
    for(var i=0;i<nodes.length;i++){
      var id=nodes[i].getAttribute('wire:id');if(!id)continue;
      var c=Livewire.find(id);if(!c)continue;
      try{var d=c.get('data');if(d!==undefined&&d!==null&&typeof d==='object')return c}catch(e){}
    }
    if(typeof Livewire.all==='function'){var a=Livewire.all();for(var j=0;j<a.length;j++){try{if(a[j].get('data')!==undefined)return a[j]}catch(e){}}}
  }catch(e){}
  return null;
}
function wm(el){if(!el||!el.attributes)return null;for(var i=0;i<el.attributes.length;i++){var n=el.attributes[i].name;if(n==='wire:model'||n.indexOf('wire:model.')===0)return el.attributes[i].value}return null}
function setPath(o,path,v){if(!path)return;var p=path.replace(/\[(\d+)\]/g,'.$1').split('.'),c=o;for(var i=0;i<p.length-1;i++){var k=p[i];if(c[k]==null||typeof c[k]!=='object')c[k]=/^\d+$/.test(p[i+1])?[]:{};c=c[k]}c[p[p.length-1]]=v}
function val(el){if(!el)return null;var t=(el.type||'').toLowerCase(),tag=(el.tagName||'').toLowerCase();if(t==='checkbox')return el.value&&el.value!=='on'?(el.checked?el.value:null):!!el.checked;if(t==='radio')return el.checked?el.value:undefined;if(tag==='select'&&el.multiple)return Array.prototype.map.call(el.selectedOptions,function(o){return o.value});return el.value}
function collect(){
  var data={};var w=findWire();
  if(w){try{var live=w.get('data');if(live&&typeof live==='object')data=JSON.parse(JSON.stringify(live))}catch(e){}}
  var root=document.querySelector('.fi-resource-create-record-page,.fi-resource-edit-record-page')||document;
  var fields=root.querySelectorAll('input,textarea,select');
  for(var i=0;i<fields.length;i++){var el=fields[i],m=wm(el);if(!m||m.indexOf('data.')!==0)continue;var v=val(el);if(v===undefined)continue;setPath(data,m.slice(5),v)}
  if(window.tinymce&&tinymce.editors){for(var e=0;e<tinymce.editors.length;e++){var ed=tinymce.editors[e];if(!ed||ed.isHidden())continue;var tg=ed.getElement&&ed.getElement(),mm=tg?wm(tg):null;if(!mm&&ed.id&&ed.id.indexOf('data.')===0)mm=ed.id;if(mm&&mm.indexOf('data.')===0){try{setPath(data,mm.slice(5),ed.getContent())}catch(err){}}}}
  return data;
}
function empty(d){if(!d||typeof d!=='object')return true;var ks=Object.keys(d);if(!ks.length)return true;for(var i=0;i<ks.length;i++){var v=d[ks[i]];if(v==null||v==='')continue;if(Array.isArray(v)&&!v.length)continue;if(typeof v==='object'&&!Array.isArray(v)&&!Object.keys(v).length)continue;return false}return true}
function status(msg,kind){
  if(!isPage())return;
  if(!S.el||!document.body.contains(S.el)){var h=document.querySelector('.fi-header-heading,h1.fi-header-heading,.fi-page-header');if(!h)return;S.el=document.createElement('div');S.el.id='alternova-autosave-status';S.el.setAttribute('aria-live','polite');S.el.style.cssText='font-size:12px;font-weight:500;margin-top:6px;opacity:.9;';h.parentNode&&h.parentNode.insertBefore(S.el,h.nextSibling)}
  if(!S.el)return;S.el.textContent=msg||'';S.el.style.color=kind==='error'?'#dc2626':(kind==='ok'?'#059669':'#64748b');
}
function saveLocal(data){
  if(!S.cfg||!S.cfg.key||empty(data))return;
  try{var payload={t:Date.now(),resource:S.cfg.resource,recordId:S.cfg.recordId,data:data};var json=JSON.stringify(payload);if(json===S.lj)return;if(json.length>1500000){status('Draft too large','error');return}localStorage.setItem(S.cfg.key,json);S.lj=json;status('Form backup on this device · '+new Date().toLocaleTimeString()+' (not in Drafts list)','ok')}catch(e){status('Could not save draft offline','error')}
}
function readLocal(){if(!S.cfg||!S.cfg.key)return null;try{var r=localStorage.getItem(S.cfg.key);return r?JSON.parse(r):null}catch(e){return null}}
function clearLocal(){if(!S.cfg||!S.cfg.key)return;try{localStorage.removeItem(S.cfg.key)}catch(e){}S.lj='';status('',null)}
function syncServer(data,force){
  if(!S.cfg||S.cfg.demo||(!S.on&&!force)||empty(data))return;var w=findWire();if(!w)return;
  var json=JSON.stringify(data),now=Date.now();if(!force&&json===S.sj)return;if(!force&&(now-S.sa)<(S.cfg.serverMs||60000))return;S.sa=now;S.sj=json;
  try{w.call('persistAutoSaveDraft',data);status('Form backup synced · '+new Date().toLocaleTimeString()+' (open Create again to resume)','ok')}catch(e){status('Offline — draft kept on this device','error')}
}
function tickL(){if(!isPage()||!S.cfg)return;saveLocal(collect())}
function tickS(){if(!S.on||!isPage()||!S.cfg)return;syncServer(collect(),false)}
function schedule(){clearInterval(S.lt);clearInterval(S.st);if(!S.cfg||!S.cfg.enabled)return;S.lt=setInterval(tickL,S.cfg.localMs||2000);S.st=setInterval(tickS,S.cfg.serverMs||60000)}
function restore(){
  if(!isPage()||!S.cfg)return;var d=readLocal();if(!d||!d.data||empty(d.data))return;
  if(d.t&&(Date.now()-d.t)>7*24*3600*1000){clearLocal();return}
  var w=findWire();if(!w){setTimeout(restore,500);return}
  var when=d.t?new Date(d.t).toLocaleString():'earlier';
  try{w.call('restoreAutoSaveDraft',d.data);status('Restored form backup from '+when,'ok')}catch(e){status('Could not restore draft','error')}
}
function onAct(){clearTimeout(S._db);S._db=setTimeout(tickL,600)}
function bind(){
  if(S.bound)return;S.bound=true;
  document.addEventListener('input',function(){if(S.cfg&&isPage())onAct()},true);
  document.addEventListener('change',function(){if(S.cfg&&isPage())onAct()},true);
  window.addEventListener('online',function(){S.on=true;status('Back online — syncing…','ok');tickL();syncServer(collect(),true)});
  window.addEventListener('offline',function(){S.on=false;tickL();status('Offline — drafts stay on this device','error')});
  window.addEventListener('beforeunload',function(){tickL()});
}
window.__alternovaAutosave={
  init:function(cfg){
    if(!cfg||!cfg.enabled||!isPage())return;bind();
    var same=S.cfg&&S.cfg.key===cfg.key;S.cfg=cfg;schedule();
    if(!same){S.el=null;status(cfg.demo?'Demo mode — offline drafts only':'Auto-save on',null)}
    if(!S.rest[cfg.key]){S.rest[cfg.key]=true;setTimeout(restore,400);setTimeout(restore,1200)}
    setTimeout(tickL,1500);
  },
  clear:function(){clearLocal();S.sj=''},
  dump:function(){return{cfg:S.cfg,draft:collect(),stored:readLocal()}}
};
document.addEventListener('livewire:init',function(){
  if(!window.Livewire)return;
  Livewire.on('autosave-clear',function(){window.__alternovaAutosave.clear()});
  Livewire.on('autosave-init',function(payload){var cfg=payload&&payload.config?payload.config:payload;if(cfg)window.__alternovaAutosave.init(cfg)});
});
document.addEventListener('livewire:navigated',function(){S.el=null});
})();
</script>
