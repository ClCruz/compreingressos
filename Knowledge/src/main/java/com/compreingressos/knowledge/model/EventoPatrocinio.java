/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.compreingressos.knowledge.model;

import java.io.Serializable;
import java.util.Date;
import javax.persistence.Column;
import javax.persistence.EmbeddedId;
import javax.persistence.Entity;
import javax.persistence.JoinColumn;
import javax.persistence.ManyToOne;
import javax.persistence.NamedQueries;
import javax.persistence.NamedQuery;
import javax.persistence.Table;
import javax.persistence.Temporal;
import javax.persistence.TemporalType;
import javax.xml.bind.annotation.XmlRootElement;

/**
 *
 * @author edicarlos.barbosa
 */
@Entity
@Table(name = "FATO_KBASE_EVENTO_PATROCINIO")
@XmlRootElement
@NamedQueries({
    @NamedQuery(name = "EventoPatrocinio.findAll", query = "SELECT e FROM EventoPatrocinio e")
})
public class EventoPatrocinio implements Serializable {
    private static final long serialVersionUID = 1L;
    @EmbeddedId
    protected EventoPatrocinioPK eventoPatrocinioPK;   
    @JoinColumn(name = "ID_KBPA", referencedColumnName = "ID_KBPA", insertable = false, updatable = false)
    @ManyToOne(optional = false)
    private Patrocinador patrocinador;
    @JoinColumn(name = "ID_KBEV", referencedColumnName = "ID_KBEV", insertable = false, updatable = false)
    @ManyToOne(optional = false)
    private Evento evento;
    @Column(name = "DT_KBEP_INICIAL")
    @Temporal(TemporalType.TIMESTAMP)
    private Date dataInicial;
    @Column(name = "DT_KBEP_FINAL")
    @Temporal(TemporalType.TIMESTAMP)
    private Date dataFinal;
    @Column(name = "VL_KBEP")
    private Double valor;

    public EventoPatrocinio() {
    }

    public EventoPatrocinio(EventoPatrocinioPK eventoPatrocinioPK) {
        this.eventoPatrocinioPK = eventoPatrocinioPK;
    }

    public EventoPatrocinio(int patrocinadorId, int eventoId) {
        this.eventoPatrocinioPK = new EventoPatrocinioPK(patrocinadorId, eventoId);
    }

    public EventoPatrocinioPK getEventoPatrocinioPK() {
        return eventoPatrocinioPK;
    }

    public void setEventoPatrocinioPK(EventoPatrocinioPK eventoPatrocinioPK) {
        this.eventoPatrocinioPK = eventoPatrocinioPK;
    }

    public Patrocinador getPatrocinador() {
        return patrocinador;
    }

    public void setPatrocinador(Patrocinador patrocinador) {
        this.patrocinador = patrocinador;
    }

    public Evento getEvento() {
        return evento;
    }

    public void setEvento(Evento evento) {
        this.evento = evento;
    }

    public Date getDataInicial() {
        return dataInicial;
    }

    public void setDataInicial(Date dataInicial) {
        this.dataInicial = dataInicial;
    }

    public Date getDataFinal() {
        return dataFinal;
    }

    public void setDataFinal(Date dataFinal) {
        this.dataFinal = dataFinal;
    }

    public Double getValor() {
        return valor;
    }

    public void setValor(Double valor) {
        this.valor = valor;
    }
    
    @Override
    public int hashCode() {
        int hash = 0;
        hash += (eventoPatrocinioPK != null ? eventoPatrocinioPK.hashCode() : 0);
        return hash;
    }

    @Override
    public boolean equals(Object object) {
        // TODO: Warning - this method won't work in the case the id fields are not set
        if (!(object instanceof EventoPatrocinio)) {
            return false;
        }
        EventoPatrocinio other = (EventoPatrocinio) object;
        if ((this.eventoPatrocinioPK == null && other.eventoPatrocinioPK != null) || (this.eventoPatrocinioPK != null && !this.eventoPatrocinioPK.equals(other.eventoPatrocinioPK))) {
            return false;
        }
        return true;
    }

    @Override
    public String toString() {
        return eventoPatrocinioPK.toString();
    }
    
}
