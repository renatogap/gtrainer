class ContagemRegressiva {

	constructor() {
		this._showContagem;
		this._tempo;
		this._btnFinalizado;
		this._exercicio;
	    this._interval;
	    this._tipo;
	}

	iniciarContagem(index) {
    	this._tipo = document.getElementById("tipo_"+index);

    	if(this._tipo === 'rep'){
    		return false;
    	}

        this._tempo = document.getElementById("tempo_"+index).value;
        this._showContagem = document.getElementById("number_"+index);
        this._showContagem.innerHTML = this._tempo;

        this._btnFinalizado = document.getElementById("finalizado_"+index);
        this._exercicio = document.getElementById("exercicio_"+index);

        this._interval = setInterval(this.myTimer.apply(this), 1000);
    }

    myTimer() {
      if(this._tempo >= 0){
          this._showContagem.innerHTML = this._tempo;
          this._tempo--;
        }else {
          this.myStopFunction;
          this._btnFinalizado.classList.remove('hidden');
          this._exercicio.style.background = '#FFFF99';
        }
    }

    myStopFunction() {
      clearInterval(this._interval);
    }

}