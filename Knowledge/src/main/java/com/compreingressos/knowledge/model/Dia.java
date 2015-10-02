/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.compreingressos.knowledge.model;

import java.io.Serializable;
import java.util.Date;
import java.util.List;
import javax.persistence.Basic;
import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.Id;
import javax.persistence.NamedQueries;
import javax.persistence.NamedQuery;
import javax.persistence.OneToMany;
import javax.persistence.Table;
import javax.persistence.Temporal;
import javax.persistence.TemporalType;
import javax.validation.constraints.NotNull;
import javax.xml.bind.annotation.XmlRootElement;
import javax.xml.bind.annotation.XmlTransient;

/**
 *
 * @author edicarlos.barbosa
 */
@Entity
@Table(name = "DIM_DIA")
@XmlRootElement
@NamedQueries({
    @NamedQuery(name = "Dia.findAll", query = "SELECT d FROM Dia d")})
public class Dia implements Serializable {
    private static final long serialVersionUID = 1L;
    @Id
    @Basic(optional = false)
    @NotNull
    @Column(name = "ID_DIA")
    private Integer idDia;
    @Column(name = "DT_DIA")
    @Temporal(TemporalType.TIMESTAMP)
    private Date dtDia;
    @Column(name = "ID_ANO")
    private Integer idAno;
    @Column(name = "ID_DIA_ANTERIOR")
    private Integer idDiaAnterior;
    @Column(name = "ID_DIA_ANO_ANTERIOR")
    private Integer idDiaAnoAnterior;
    @Column(name = "ID_SEMESTRE")
    private Integer idSemestre;
    @Column(name = "ID_TRIMESTRE")
    private Integer idTrimestre;
    @Column(name = "ID_MES")
    private Integer idMes;
    @Column(name = "ID_QUINZENA_MES")
    private Integer idQuinzenaMes;
    @Column(name = "ID_DIA_SEMANA")
    private Integer idDiaSemana;
    @OneToMany(mappedBy = "dia")
    private List<Apresentacao> apresentacaoList;

    public Dia() {
    }

    public Dia(Integer idDia) {
        this.idDia = idDia;
    }

    public Integer getIdDia() {
        return idDia;
    }

    public void setIdDia(Integer idDia) {
        this.idDia = idDia;
    }

    public Date getDtDia() {
        return dtDia;
    }

    public void setDtDia(Date dtDia) {
        this.dtDia = dtDia;
    }

    public Integer getIdAno() {
        return idAno;
    }

    public void setIdAno(Integer idAno) {
        this.idAno = idAno;
    }

    public Integer getIdDiaAnterior() {
        return idDiaAnterior;
    }

    public void setIdDiaAnterior(Integer idDiaAnterior) {
        this.idDiaAnterior = idDiaAnterior;
    }

    public Integer getIdDiaAnoAnterior() {
        return idDiaAnoAnterior;
    }

    public void setIdDiaAnoAnterior(Integer idDiaAnoAnterior) {
        this.idDiaAnoAnterior = idDiaAnoAnterior;
    }

    public Integer getIdSemestre() {
        return idSemestre;
    }

    public void setIdSemestre(Integer idSemestre) {
        this.idSemestre = idSemestre;
    }

    public Integer getIdTrimestre() {
        return idTrimestre;
    }

    public void setIdTrimestre(Integer idTrimestre) {
        this.idTrimestre = idTrimestre;
    }

    public Integer getIdMes() {
        return idMes;
    }

    public void setIdMes(Integer idMes) {
        this.idMes = idMes;
    }

    public Integer getIdQuinzenaMes() {
        return idQuinzenaMes;
    }

    public void setIdQuinzenaMes(Integer idQuinzenaMes) {
        this.idQuinzenaMes = idQuinzenaMes;
    }

    public Integer getIdDiaSemana() {
        return idDiaSemana;
    }

    public void setIdDiaSemana(Integer idDiaSemana) {
        this.idDiaSemana = idDiaSemana;
    }

    @XmlTransient
    public List<Apresentacao> getApresentacaoList() {
        return apresentacaoList;
    }

    public void setApresentacaoList(List<Apresentacao> apresentacaoList) {
        this.apresentacaoList = apresentacaoList;
    }

    @Override
    public int hashCode() {
        int hash = 0;
        hash += (idDia != null ? idDia.hashCode() : 0);
        return hash;
    }

    @Override
    public boolean equals(Object object) {
        // TODO: Warning - this method won't work in the case the id fields are not set
        if (!(object instanceof Dia)) {
            return false;
        }
        Dia other = (Dia) object;
        if ((this.idDia == null && other.idDia != null) || (this.idDia != null && !this.idDia.equals(other.idDia))) {
            return false;
        }
        return true;
    }

    @Override
    public String toString() {
        return "com.compreingressos.knowledge.model.Dia[ idDia=" + idDia + " ]";
    }
    
}
