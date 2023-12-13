(()=>{"use strict";const e=window.React,t=window.wp.element,n=window.wc.wcBlocksRegistry,c=window.wp.htmlEntities,a=window.wc.wcSettings,i=({title:t,description:n,linkText:c,linkSrc:a})=>(0,e.createElement)("div",{className:"mp-checkout-pro-test-mode"},(0,e.createElement)("test-mode",{title:t,description:n,"link-text":c,"link-src":a})),o=({name:t,buttonName:n,columns:c})=>(0,e.createElement)("input-table",{name:t,"button-name":n,columns:c}),r=({isVisible:t,message:n,inputId:c,id:a,dataMain:i})=>(0,e.createElement)("input-helper",{isVisible:t,message:n,"input-id":c,id:a,"data-main":i}),s=({labelMessage:t,helperMessage:n,inputName:c,hiddenId:a,inputDataCheckout:i,selectId:o,selectName:r,selectDataCheckout:s,flagError:l,documents:m,validate:d})=>(0,e.createElement)("div",{className:"mp-checkout-ticket-input-document"},(0,e.createElement)("input-document",{"label-message":t,"helper-message":n,"input-name":c,"hidden-id":a,"input-data-checkout":i,"select-id":o,"select-name":r,"select-data-checkout":s,"flag-error":l,documents:m,validate:d})),l=({description:t,linkText:n,linkSrc:c,checkoutClass:a="pro"})=>(0,e.createElement)("div",{className:`mp-checkout-${a}-terms-and-conditions`},(0,e.createElement)("terms-and-conditions",{description:t,"link-text":n,"link-src":c}));var m;const d=(0,a.getSetting)("woo-mercado-pago-ticket_data",{}),u=(0,c.decodeEntities)(d.title)||"Checkout Ticket",p=n=>{const{test_mode_title:c,test_mode_description:a,test_mode_link_text:m,test_mode_link_src:u,input_document_label:p,input_document_helper:_,ticket_text_label:k,input_table_button:h,input_helper_label:g,payment_methods:E,currency_ratio:y,amount:b,site_id:w,terms_and_conditions_description:N,terms_and_conditions_link_text:S,terms_and_conditions_link_src:v,test_mode:f}=d.params,x=(0,t.useRef)(null),{eventRegistration:C,emitResponse:M}=n,{onPaymentSetup:T}=C;let R={labelMessage:p,helperMessage:_,validate:"true",selectId:"docType",flagError:"mercadopago_ticket[docNumberError]",inputName:"mercadopago_ticket[docNumber]",selectName:"mercadopago_ticket[docType]",documents:null};return"MLB"===w?R.documents='["CPF","CNPJ"]':"MLU"===w&&(R.documents='["CI","OTRO"]'),(0,t.useEffect)((()=>{const e=T((async()=>{const e={};e["mercadopago_ticket[amount]"]=b.toString(),e["mercadopago_ticket[doc_type]"]=x.current.querySelector("#docType").value,e["mercadopago_ticket[doc_number]"]=x.current.querySelector("#form-checkout__identificationNumber-container > input").value;const t=E.find((e=>{const t=`#${e.id}`,n=x.current.querySelector(t);return n&&n.checked}));return t&&(e["mercadopago_ticket[payment_method_id]"]=x.current.querySelector(`#${t.id}`).value),{type:M.responseTypes.SUCCESS,meta:{paymentMethodData:e}}}));return()=>e()}),[M.responseTypes.ERROR,M.responseTypes.SUCCESS,T]),(0,e.createElement)("div",{className:"mp-checkout-container"},(0,e.createElement)("div",{className:"mp-checkout-ticket-container"},(0,e.createElement)("div",{ref:x,className:"mp-checkout-ticket-content"},f?(0,e.createElement)(i,{title:c,description:a,"link-text":m,"link-src":u}):null,R?(0,e.createElement)(s,{...R}):null,(0,e.createElement)("p",{className:"mp-checkout-ticket-tex"},k),(0,e.createElement)(o,{name:"mercadopago_ticket[payment_method_id]",buttonName:h,columns:JSON.stringify(E)}),(0,e.createElement)(r,{isVisible:"false",message:g,inputId:"mp-payment-method-helper",id:"payment-method-helper"}),(0,e.createElement)("div",{id:"mp-box-loading"}))),(0,e.createElement)(l,{description:N,linkText:S,linkSrc:v,checkoutClass:"ticket"}))},_={name:"woo-mercado-pago-ticket",label:(0,e.createElement)((t=>{const{PaymentMethodLabel:n}=t.components;return(0,e.createElement)(n,{text:u})}),null),content:(0,e.createElement)(p,null),edit:(0,e.createElement)(p,null),canMakePayment:()=>!0,ariaLabel:u,supports:{features:null!==(m=d?.supports)&&void 0!==m?m:[]}};(0,n.registerPaymentMethod)(_)})();