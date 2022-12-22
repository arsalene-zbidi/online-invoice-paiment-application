
let ref ="";
function getRef(Ref){
    this.ref=Ref;

}



function paiment() {
    const ele = document.getElementsByName('choix');
   let choix='';
   let ref='';
    for(i = 0; i < ele.length; i++) {
        if(ele[i].checked){

            choix=ele[i].value;
            ref=this.ref;

       $.ajax({
            url : "/choix",
            type: "POST",
            datatype: "json",
            data: {
                'choix': ele[i].value,
                'Ref':ref,
            },
            error: (error) => {
                console.log(JSON.stringify(error));
            },
            success: function(data) {
                //data will hold an object with your response data, no need to parse

                window.document.location = '/paiment/'+ref+'/'+choix;
            }

        });}
    }

}

function getdata() {

    const cin = document.getElementById('form_Cin').value;
    const Rib = document.getElementById('form_Rib').value;
    $.ajax({
        url : "/paimentVirement/{Rib}",
        type: "POST",
        datatype: "json",
        data: {
            'cin':cin,
            'Rib':Rib,
        },
        error: (error) => {
            console.log(JSON.stringify(error));
        },
        success: function(data) {
            //data will hold an object with your response data, no need to parse
            alert (this.data.Rib);
        }

    });


}