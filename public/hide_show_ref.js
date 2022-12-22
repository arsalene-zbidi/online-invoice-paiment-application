function HideandShow(){
    const ele = document.getElementsByName('choix');
    const Ref=document.getElementById('myref')
    for(i = 0; i < ele.length; i++) {
        if(ele[i].checked){
            if(ele[i].value==="seule"){

                Ref.style.display='block';
            }else{
                Ref.style.display='none';
            }



        }}

}