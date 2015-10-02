/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.compreingressos.knowledge.model;

import java.io.Serializable;
import java.util.List;
import javax.persistence.Basic;
import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.Id;
import javax.persistence.NamedQueries;
import javax.persistence.NamedQuery;
import javax.persistence.OneToMany;
import javax.persistence.Table;
import javax.validation.constraints.NotNull;
import javax.validation.constraints.Size;
import javax.xml.bind.annotation.XmlRootElement;
import javax.xml.bind.annotation.XmlTransient;

/**
 *
 * @author edicarlos.barbosa
 */
@Entity
@Table(name = "DIM_MES")
@XmlRootElement
@NamedQueries({
    @NamedQuery(name = "Mes.findAll", query = "SELECT m FROM Mes m")})
public class Mes implements Serializable {
    private static final long serialVersionUID = 1L;
    @Id
    @Basic(optional = false)
    @NotNull
    @Column(name = "ID_MES")
    private Integer idMes;
    @Basic(optional = false)
    @NotNull
    @Size(min = 1, max = 30)
    @Column(name = "DS_MES")
    private String dsMes;
    @Basic(optional = false)
    @NotNull
    @Column(name = "ID_MES_NO_ANO")
    private int idMesNoAno;
    @Basic(optional = false)
    @NotNull
    @Column(name = "ID_MES_ANO_ANTERIOR")
    private int idMesAnoAnterior;
    @Basic(optional = false)
    @NotNull
    @Column(name = "ID_MES_ANTERIOR")
    private int idMesAnterior;
    @Basic(optional = false)
    @NotNull
    @Column(name = "ID_ANO")
    private int idAno;
    @Basic(optional = false)
    @NotNull
    @Column(name = "ID_SEMESTRE")
    private int idSemestre;
    @Basic(optional = false)
    @NotNull
    @Column(name = "ID_TRIMESTRE")
    private int idTrimestre;
    @OneToMany(mappedBy = "mes")
    private List<Apresentacao> apresentacaoList;

    public Mes() {
    }

    public Mes(Integer idMes) {
        this.idMes = idMes;
    }

    public Mes(Integer idMes, String dsMes, int idMesNoAno, int idMesAnoAnterior, int idMesAnterior, int idAno, int idSemestre, int idTrimestre) {
        this.idMes = idMes;
        this.dsMes = dsMes;
        this.idMesNoAno = idMesNoAno;
        this.idMesAnoAnterior = idMesAnoAnterior;
        this.idMesAnterior = idMesAnterior;
        this.idAno = idAno;
        this.idSemestre = idSemestre;
        this.idTrimestre = idTrimestre;
    }

    public Integer getIdMes() {
        return idMes;
    }

    public void setIdMes(Integer idMes) {
        this.idMes = idMes;
    }

    public String getDsMes() {
        return dsMes;
    }

    public void setDsMes(String dsMes) {
        this.dsMes = dsMes;
    }

    public int getIdMesNoAno() {
        return idMesNoAno;
    }

    public void setIdMesNoAno(int idMesNoAno) {
        this.idMesNoAno = idMesNoAno;
    }

    public int getIdMesAnoAnterior() {
        return idMesAnoAnterior;
    }

    public void setIdMesAnoAnterior(int idMesAnoAnterior) {
        this.idMesAnoAnterior = idMesAnoAnterior;
    }

    public int getIdMesAnterior() {
        return idMesAnterior;
    }

    public void setIdMesAnterior(int idMesAnterior) {
        this.idMesAnterior = idMesAnterior;
    }

    public int getIdAno() {
        return idAno;
    }

    public void setIdAno(int idAno) {
        this.idAno = idAno;
    }

    public int getIdSemestre() {
        return idSemestre;
    }

    public void setIdSemestre(int idSemestre) {
        this.idSemestre = idSemestre;
    }

    public int getIdTrimestre() {
        return idTrimestre;
    }

    public void setIdTrimestre(int idTrimestre) {
        this.idTrimestre = idTrimestre;
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
        hash += (idMes != null ? idMes.hashCode() : 0);
        return hash;
    }

    @Override
    public boolean equals(Object object) {
        // TODO: Warning - this method won't work in the case the id fields are not set
        if (!(object instanceof Mes)) {
            return false;
        }
        Mes other = (Mes) object;
        if ((this.idMes == null && other.idMes != null) || (this.idMes != null && !this.idMes.equals(other.idMes))) {
            return false;
        }
        return true;
    }

    @Override
    public String toString() {
        return "com.compreingressos.knowledge.model.Mes[ idMes=" + idMes + " ]";
    }
    
}
