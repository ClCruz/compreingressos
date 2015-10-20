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
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;
import javax.persistence.JoinColumn;
import javax.persistence.ManyToOne;
import javax.persistence.NamedQueries;
import javax.persistence.NamedQuery;
import javax.persistence.Table;
import javax.persistence.Temporal;
import javax.persistence.TemporalType;
import javax.persistence.Transient;
import javax.validation.constraints.NotNull;
import javax.validation.constraints.Size;
import javax.xml.bind.annotation.XmlRootElement;
import javax.xml.bind.annotation.XmlTransient;

/**
 *
 * @author edicarlos.barbosa
 */
@Entity
@Table(name = "FATO_KBASE_APRESENTACAO")
@XmlRootElement
@NamedQueries({
    @NamedQuery(name = "Apresentacao.findAll", query = "SELECT a FROM Apresentacao a")})
public class Apresentacao implements Serializable {
    private static final long serialVersionUID = 1L;
    @Id
    @Basic(optional = false)
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "ID_KBAP")
    private Integer id;
    @Basic(optional = false)
    @NotNull
    @Column(name = "DT_KBAP_APRESENTACAO")
    @Temporal(TemporalType.TIMESTAMP)
    private Date data;
    @Size(max = 5)
    @Column(name = "HR_KBAP_APRESENTACAO")
    private String hora;
    // @Max(value=?)  @Min(value=?)//if you know range of your decimal fields consider using these annotations to enforce field validation
    @Column(name = "VL_KBAP_INGRESSO")
    private Double valorIngresso;
    @Basic(optional = false)
    @NotNull
    @Column(name = "DT_ATUALIZACAO")
    @Temporal(TemporalType.TIMESTAMP)
    private Date dataAtualizacao;
    @JoinColumn(name = "ID_MES", referencedColumnName = "ID_MES")
    @ManyToOne
    private Mes mes;
    @JoinColumn(name = "ID_KBEV", referencedColumnName = "ID_KBEV")
    @ManyToOne
    private Evento evento;
    @JoinColumn(name = "ID_DIA", referencedColumnName = "ID_DIA")
    @ManyToOne
    private Dia dia;
    @Transient
    private Date dataFinal;   

    public Apresentacao() {
    }

    public Apresentacao(Integer id) {
        this.id = id;
    }

    public Apresentacao(Integer id, Date dtApresentacao, Date dtAtualizacao) {
        this.id = id;
        this.data = dtApresentacao;
        this.dataAtualizacao = dtAtualizacao;
    }

    public Integer getId() {
        return id;
    }

    public void setId(Integer id) {
        this.id = id;
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

    public Double getValorIngresso() {
        return valorIngresso;
    }

    public void setValorIngresso(Double valorIngresso) {
        this.valorIngresso = valorIngresso;
    }

    public Date getDataAtualizacao() {
        return dataAtualizacao;
    }

    public void setDataAtualizacao(Date dataAtualizacao) {
        this.dataAtualizacao = dataAtualizacao;
    }

    public Mes getMes() {
        return mes;
    }

    public void setMes(Mes mes) {
        this.mes = mes;
    }

    public Evento getEvento() {
        return evento;
    }

    public void setEvento(Evento evento) {
        this.evento = evento;
    }

    public Dia getDia() {
        return dia;
    }

    public void setDia(Dia dia) {
        this.dia = dia;
    }

    @XmlTransient
    public Date getDataFinal() {
        return dataFinal;
    }

    public void setDataFinal(Date dataFinal) {
        this.dataFinal = dataFinal;
    }       
            
    @Override
    public int hashCode() {
        int hash = 0;
        hash += (id != null ? id.hashCode() : 0);
        return hash;
    }

    @Override
    public boolean equals(Object object) {
        // TODO: Warning - this method won't work in the case the id fields are not set
        if (!(object instanceof Apresentacao)) {
            return false;
        }
        Apresentacao other = (Apresentacao) object;
        if ((this.id == null && other.id != null) || (this.id != null && !this.id.equals(other.id))) {
            return false;
        }
        return true;
    }

    @Override
    public String toString() {
        return id.toString();
    }
    
}
