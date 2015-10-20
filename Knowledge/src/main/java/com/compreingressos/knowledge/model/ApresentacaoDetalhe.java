package com.compreingressos.knowledge.model;

import java.util.Date;

/**
 *
 * @author edicarlos.barbosa
 */
public class ApresentacaoDetalhe {
        
    private Date data;
    private String hora;
    private Double valor;
    private boolean selected;

    public ApresentacaoDetalhe() {        
    }

    public ApresentacaoDetalhe(boolean selected) {
        this.selected = selected;
    }
            
    public ApresentacaoDetalhe(Date data, String hora, Double valor) {
        this.data = data;
        this.hora = hora;
        this.valor = valor;
    }
        
    public Date getData() {
        return data;
    }

    public void setData(Date data) {
        this.data = data;
    }

    public String getHora() {
        return hora;
    }

    public void setHora(String hora) {
        this.hora = hora;
    }

    public Double getValor() {
        return valor;
    }

    public void setValor(Double valor) {
        this.valor = valor;
    }

    public boolean isSelected() {
        return selected;
    }

    public void setSelected(boolean selected) {
        this.selected = selected;
    }
    
        
}
