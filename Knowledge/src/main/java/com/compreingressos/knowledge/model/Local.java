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
import javax.persistence.OneToMany;
import javax.persistence.Table;
import javax.persistence.Temporal;
import javax.persistence.TemporalType;
import javax.validation.constraints.NotNull;
import javax.validation.constraints.Size;
import javax.xml.bind.annotation.XmlRootElement;
import javax.xml.bind.annotation.XmlTransient;

/**
 *
 * @author edicarlos.barbosa
 */
@Entity
@Table(name = "DIM_KBASE_LOCAL")
@XmlRootElement
@NamedQueries({
    @NamedQuery(name = "Local.findAll", query = "SELECT l FROM Local l ORDER BY l.descricao")})
public class Local implements Serializable {
    private static final long serialVersionUID = 1L;
    @Id
    @Basic(optional = false)
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "ID_KBLO")
    private Integer id;
    @Basic(optional = false)
    @NotNull
    @Size(min = 1, max = 60)
    @Column(name = "DS_KBLO")
    private String descricao;
    @Size(max = 150)
    @Column(name = "DS_KBLO_ENDERECO")
    private String endereco;
    @Size(max = 50)
    @Column(name = "DS_KBLO_BAIRRO")
    private String bairro;
    @Size(max = 8)
    @Column(name = "CD_KBLO_CEP")
    private String cep;
    @Size(max = 15)
    @Column(name = "CD_KBLO_TELEFONE")
    private String telefone;
    @Size(max = 15)
    @Column(name = "CD_KBLO_FAX")
    private String fax;
    @Column(name = "QT_KBLO_LUGARES")
    private Integer quantidadeLugares;
    @Size(max = 18)
    @Column(name = "IN_KBLO_TIPO_PROPRIEDADE")
    private String tipoPropriedade;
    @Basic(optional = false)
    @NotNull
    @Column(name = "DT_ATUALIZACAO")
    @Temporal(TemporalType.TIMESTAMP)
    private Date dtAtualizacao;
    @Size(max = 60)
    @Column(name = "CD_KBLO_SITE")
    private String site;
    @Size(max = 60)
    @Column(name = "NM_KBLO_PRESIDENTE")
    private String nomePresidente;
    @Size(max = 60)
    @Column(name = "NM_KBLO_DIR_TEATRO")
    private String nomeDiretorTeatro;
    @Size(max = 60)
    @Column(name = "NM_KBLO_DIR_PAUTA")
    private String nomeDiretorPauta;
    @Size(max = 100)
    @Column(name = "CD_KBLO_EMAIL_DIR_PAUTA")
    private String emailDiretorPauta;
    @Size(max = 200)
    @Column(name = "DS_KBLO_OBS")
    private String observacao;
    @JoinColumn(name = "ID_MUNICIPIO", referencedColumnName = "ID_MUNICIPIO")
    @ManyToOne
    private Municipio municipio;
    @JoinColumn(name = "ID_KBTL", referencedColumnName = "ID_KBTL")
    @ManyToOne
    private TipoLocal tipoLocal;
    @OneToMany(mappedBy = "local")
    private List<Evento> listaEvento;

    public Local() {
    }

    public Local(Integer id) {
        this.id = id;
    }

    public Local(Integer id, String descricao, Date dtAtualizacao) {
        this.id = id;
        this.descricao = descricao;
        this.dtAtualizacao = dtAtualizacao;
    }

    public Integer getId() {
        return id;
    }

    public void setId(Integer id) {
        this.id = id;
    }

    public String getDescricao() {
        return descricao;
    }

    public void setDescricao(String descricao) {
        this.descricao = descricao;
    }

    public String getEndereco() {
        return endereco;
    }

    public void setEndereco(String endereco) {
        this.endereco = endereco;
    }

    public String getBairro() {
        return bairro;
    }

    public void setBairro(String bairro) {
        this.bairro = bairro;
    }

    public String getCep() {
        return cep;
    }

    public void setCep(String cep) {
        this.cep = cep;
    }

    public String getTelefone() {
        return telefone;
    }

    public void setTelefone(String telefone) {
        this.telefone = telefone;
    }

    public String getFax() {
        return fax;
    }

    public void setFax(String fax) {
        this.fax = fax;
    }

    public Integer getQuantidadeLugares() {
        return quantidadeLugares;
    }

    public void setQuantidadeLugares(Integer quantidadeLugares) {
        this.quantidadeLugares = quantidadeLugares;
    }

    public String getTipoPropriedade() {
        return tipoPropriedade;
    }

    public void setTipoPropriedade(String tipoPropriedade) {
        this.tipoPropriedade = tipoPropriedade;
    }

    public Date getDtAtualizacao() {
        return dtAtualizacao;
    }

    public void setDtAtualizacao(Date dtAtualizacao) {
        this.dtAtualizacao = dtAtualizacao;
    }

    public String getSite() {
        return site;
    }

    public void setSite(String site) {
        this.site = site;
    }

    public String getNomePresidente() {
        return nomePresidente;
    }

    public void setNomePresidente(String nomePresidente) {
        this.nomePresidente = nomePresidente;
    }

    public String getNomeDiretorTeatro() {
        return nomeDiretorTeatro;
    }

    public void setNomeDiretorTeatro(String nomeDiretorTeatro) {
        this.nomeDiretorTeatro = nomeDiretorTeatro;
    }

    public String getNomeDiretorPauta() {
        return nomeDiretorPauta;
    }

    public void setNomeDiretorPauta(String nomeDiretorPauta) {
        this.nomeDiretorPauta = nomeDiretorPauta;
    }

    public String getEmailDiretorPauta() {
        return emailDiretorPauta;
    }

    public void setEmailDiretorPauta(String emailDiretorPauta) {
        this.emailDiretorPauta = emailDiretorPauta;
    }

    public String getObservacao() {
        return observacao;
    }

    public void setObservacao(String observacao) {
        this.observacao = observacao;
    }

    public Municipio getMunicipio() {
        return municipio;
    }

    public void setMunicipio(Municipio municipio) {
        this.municipio = municipio;
    }

    public TipoLocal getTipoLocal() {
        return tipoLocal;
    }

    public void setTipoLocal(TipoLocal tipoLocal) {
        this.tipoLocal = tipoLocal;
    }

    @XmlTransient
    public List<Evento> getListaEvento() {
        return listaEvento;
    }

    public void setListaEvento(List<Evento> listaEvento) {
        this.listaEvento = listaEvento;
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
        if (!(object instanceof Local)) {
            return false;
        }
        Local other = (Local) object;
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
