/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.compreingressos.knowledge.model;

import java.io.Serializable;
import javax.persistence.Basic;
import javax.persistence.Column;
import javax.persistence.Embeddable;
import javax.validation.constraints.NotNull;

/**
 *
 * @author edicarlos.barbosa
 */
@Embeddable
public class EventoPatrocinioPK implements Serializable {
    @Basic(optional = false)
    @NotNull
    @Column(name = "ID_KBPA")
    private int patrocinadorId;
    @Basic(optional = false)
    @NotNull
    @Column(name = "ID_KBEV")
    private int eventoId;

    public EventoPatrocinioPK() {
    }

    public EventoPatrocinioPK(int patrocinadorId, int eventoId) {
        this.patrocinadorId = patrocinadorId;
        this.eventoId = eventoId;
    }

    public int getPatrocinadorId() {
        return patrocinadorId;
    }

    public void setPatrocinadorId(int patrocinadorId) {
        this.patrocinadorId = patrocinadorId;
    }

    public int getEventoId() {
        return eventoId;
    }

    public void setEventoId(int eventoId) {
        this.eventoId = eventoId;
    }

    @Override
    public int hashCode() {
        int hash = 0;
        hash += (int) patrocinadorId;
        hash += (int) eventoId;
        return hash;
    }

    @Override
    public boolean equals(Object object) {
        // TODO: Warning - this method won't work in the case the id fields are not set
        if (!(object instanceof EventoPatrocinioPK)) {
            return false;
        }
        EventoPatrocinioPK other = (EventoPatrocinioPK) object;
        if (this.patrocinadorId != other.patrocinadorId) {
            return false;
        }
        if (this.eventoId != other.eventoId) {
            return false;
        }
        return true;
    }

    @Override
    public String toString() {
        return patrocinadorId + "," + eventoId;
    }
    
}
